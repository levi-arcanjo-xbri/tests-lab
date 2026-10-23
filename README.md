# Estudo Arquitetura

Sistema de organização de projetos e tasks. Cada projeto tem várias tasks, e cada task está em "To do" ou "Done".

O projeto serve de base de estudo da stack e da arquitetura: os arquivos têm comentários explicando o papel de cada um e os trechos importantes. A seção [Roteiro de estudo](#roteiro-de-estudo) indica por onde começar.

## Funcionalidades

- Cadastro, login e logout com token Bearer (Sanctum).
- Projetos: listar com busca e progresso, criar, editar e apagar.
- Tasks: adicionar, editar o título, concluir e reabrir, filtrar por estado e apagar.
- Histórico de estados de cada task, com autor e data.
- Cada usuário só vê e altera os próprios projetos.
- Criações e mudanças de estado aceitam `Idempotency-Key`: o reenvio não duplica.
- Tema claro e escuro (segue o sistema operacional) e layout responsivo.

As regras de arquitetura, stack e qualidade estão em [CLAUDE.md](CLAUDE.md).

## Subir o ambiente

Pré-requisitos: Linux ou WSL2, Docker e Docker Compose.

```bash
docker compose up -d
```

Esse único comando:

- sobe PostgreSQL 17 (com os bancos `estudo` e `estudo_test`) e Redis 7;
- no backend, instala as dependências do Composer, cria o `backend/.env` a partir do `.env.example`, gera a `APP_KEY` e roda as migrations com o seed;
- no frontend, roda `npm ci` e o `ng serve` com proxy para a API.

A primeira subida demora alguns minutos por causa das dependências. Use `docker compose up -d --wait` para o comando só retornar quando tudo estiver saudável.

| Serviço | Endereço |
|---|---|
| Frontend (Angular) | http://localhost:4300 |
| API (Laravel) | http://localhost:8080/api |
| Documentação OpenAPI | http://localhost:8080/docs/api |
| PostgreSQL | localhost:5442 |
| Redis | localhost:6389 |

As portas e o UID/GID podem ser trocados num `.env` na raiz (veja [.env.example](.env.example)). O padrão evita conflito com as portas 4200, 8000 e 5432.

**Usuário de exemplo (seed):** `demo@example.com` / `password`.

## Comandos do dia a dia

Todos rodam dentro dos containers.

```bash
scripts/check.sh              # lint + testes de tudo (igual ao CI)
scripts/check.sh backend      # Pint + Pest
scripts/check.sh frontend     # ESLint + testes unitários do Angular
scripts/check.sh e2e          # Playwright contra o ambiente do compose

scripts/artisan.sh make:migration create_projects_table
docker compose exec backend composer require <pacote>
docker compose exec backend ./vendor/bin/pint          # corrige o estilo do PHP
docker compose exec frontend npx ng generate component features/projects/project-list
docker compose exec backend php artisan scramble:export # gera api.json
```

## Estrutura

```text
.
├── backend/                 Laravel 12 (API)
│   ├── app/
│   │   ├── Domain/          Código por contexto de negócio (DDD)
│   │   │   ├── Auth/        Cadastro, login, logout e usuário autenticado
│   │   │   ├── Project/     Projetos e a policy de titularidade
│   │   │   └── Task/        Tasks, máquina de estados e histórico
│   │   └── Infrastructure/  Mecanismos técnicos (idempotência)
│   ├── routes/api.php
│   └── tests/               Pest: Unit/ e Feature/ (PostgreSQL real)
├── frontend/                Angular 20 (sem SSR)
│   └── src/app/
│       ├── core/            Auth, interceptor, helpers de API, toasts
│       ├── layout/          Shell das páginas autenticadas
│       ├── features/        auth/, projects/, tasks/ (páginas, componentes, serviços)
│       └── shared/          Componentes reutilizáveis (ícones, toasts)
├── e2e/                     Playwright
├── docker/                  Dockerfiles e scripts de inicialização
├── scripts/                 Scripts bash de apoio
└── .github/workflows/ci.yml Lint e testes em todo PR
```

Cada contexto em `backend/app/Domain/<Contexto>` segue as camadas do CLAUDE.md:

| Pasta | Papel |
|---|---|
| `Http/Controllers` | Um controller invocável por ação. Converte a entrada e chama a ação. |
| `Http/Requests` | FormRequest com a validação. |
| `Http/Resources` | Formato da resposta JSON. |
| `Actions` | Regra de negócio e transação. |
| `Models` | Models Eloquent. |
| `Policies` | Autorização por recurso (só em `Project`). |
| `States` / `Exceptions` | Máquina de estados e erros de domínio (só em `Task`). |

### Caminho de uma requisição

Exemplo: concluir uma task com `PATCH /api/projects/1/tasks/3/status`.

```text
routes/api.php
  └─ middlewares: auth:sanctum → can:update,project (ProjectPolicy) → idempotent
       └─ TransitionTaskStatusController            (converte a entrada)
            ├─ TransitionTaskStatusRequest           (valida: status é um estado conhecido?)
            └─ TransitionTaskStatusAction            (regra de negócio, numa transação)
                 ├─ SELECT ... FOR UPDATE            (lockForUpdate contra concorrência)
                 ├─ TaskStateMachine                 (a transição é permitida? senão 409)
                 ├─ UPDATE tasks                     (status + completed_at)
                 └─ INSERT task_status_histories     (histórico na mesma transação)
       └─ TaskResource                               (formato JSON da resposta)
```

### Invariantes e onde ficam

| Regra | Aplicação | Banco |
|---|---|---|
| Só o dono acessa o projeto e as tasks | `ProjectPolicy` (responde 404) e `scopeBindings()` nas rotas | FK `projects.user_id` |
| Nome de projeto único por dono | `StoreProjectRequest` / `UpdateProjectRequest` | índice único `(user_id, name)` |
| Estado só muda por transição permitida | `TaskStateMachine` | `CHECK` do enum `status` |
| Task concluída tem `completed_at`, pendente não | `TransitionTaskStatusAction` | `CHECK tasks_completed_at_matches_status` |
| Toda mudança de estado tem histórico | transação na Action | FK `task_status_histories.task_id` |
| Reenvio não duplica | middleware `EnsureIdempotency` | índice único `(user_id, key)` |

## API

Autenticação por token do Sanctum, enviado como `Authorization: Bearer <token>`.

| Método | Rota | Descrição |
|---|---|---|
| POST | `/api/auth/register` | Cadastra e devolve `token` e `user` (201). |
| POST | `/api/auth/login` | Recebe `email` e `password`, devolve `token` e `user`. |
| POST | `/api/auth/logout` | Revoga o token atual. |
| GET | `/api/auth/me` | Devolve o usuário autenticado. |
| GET | `/api/projects?search=` | Lista os projetos do usuário, com `tasks_count` e `done_tasks_count`. |
| POST | `/api/projects` | Cria um projeto (201). Aceita `Idempotency-Key`. |
| GET | `/api/projects/{id}` | Mostra um projeto. |
| PATCH | `/api/projects/{id}` | Altera `name` e/ou `description`. |
| DELETE | `/api/projects/{id}` | Apaga o projeto e as tasks (204). |
| GET | `/api/projects/{id}/tasks?status=` | Lista as tasks (pendentes primeiro), com filtro `todo` ou `done`. |
| POST | `/api/projects/{id}/tasks` | Cria uma task em `todo` (201). Aceita `Idempotency-Key`. |
| PATCH | `/api/projects/{id}/tasks/{taskId}` | Altera `title` e/ou `description`. |
| DELETE | `/api/projects/{id}/tasks/{taskId}` | Apaga a task (204). |
| PATCH | `/api/projects/{id}/tasks/{taskId}/status` | Muda o estado: `{ "status": "done" }`. Aceita `Idempotency-Key`. |
| GET | `/api/projects/{id}/tasks/{taskId}/history` | Histórico de estados, com autor. |
| GET | `/up` | Health check. |

Códigos de erro:

| Código | Quando |
|---|---|
| 401 | Sem token ou token inválido. |
| 404 | Recurso inexistente ou de outro usuário (a API não revela qual dos dois). |
| 409 | Transição de estado não permitida, ou `Idempotency-Key` com requisição ainda em andamento. |
| 422 | Validação (`{ message, errors }`) ou `Idempotency-Key` reaproveitada com outro corpo. |
| 429 | Muitas tentativas de login ou cadastro para o mesmo e-mail e IP. |

A documentação completa é gerada a partir do código pelo Scramble em `/docs/api` (disponível em ambiente local).

### Máquina de estados da task

```text
todo ──concluir──▶ done
done ──reabrir───▶ todo
```

As transições ficam declaradas só em `backend/app/Domain/Task/States/TaskStateMachine.php`. A API devolve `allowed_transitions` em cada task, e o frontend usa essa lista em vez de repetir a regra.

### Idempotência

Envie um UUID no cabeçalho `Idempotency-Key` em `POST /projects`, `POST /tasks` ou `PATCH /status`:

- mesma chave e mesmo corpo depois de um sucesso: a API devolve a resposta guardada, com `Idempotent-Replayed: true`, sem executar de novo;
- mesma chave e outro corpo: 422;
- se a primeira tentativa falhou, a chave fica livre para uma nova tentativa.

As chaves são separadas por usuário e apagadas depois de `IDEMPOTENCY_TTL_HOURS` (24 h por padrão) pelo `model:prune` agendado em `routes/console.php`. O frontend gera a chave em `core/api/idempotency.ts` e só a troca depois de um sucesso.

## Roteiro de estudo

1. **Ambiente:** `docker-compose.yml`, `docker/php/entrypoint.sh` e `docker/node/entrypoint.sh`.
2. **Banco:** as migrations em `backend/database/migrations`, com as invariantes comentadas.
3. **Uma requisição de ponta a ponta:** siga o [caminho de uma requisição](#caminho-de-uma-requisição) a partir de `backend/routes/api.php`.
4. **Regras de domínio:** `TaskStateMachine`, `TransitionTaskStatusAction` e `ProjectPolicy`.
5. **Infraestrutura:** `backend/app/Infrastructure/Idempotency/EnsureIdempotency.php`.
6. **Testes do backend:** `backend/tests/Feature` (API com banco real) e `backend/tests/Unit` (lógica pura).
7. **Frontend:** `app.config.ts` e `app.routes.ts`; depois `core/auth` (sessão, interceptor, guards), `features/projects` (páginas e serviços) e `features/tasks/task-item`.
8. **Testes do frontend e E2E:** os arquivos `*.spec.ts` e `e2e/tests`.

Para experimentar a API direto, use a interface do Scramble em http://localhost:8080/docs/api.

## Eventos e cenários externos

Ainda não há eventos nem integrações externas. Quando houver, os contratos (no envelope padrão do CLAUDE.md) e a origem de cada cenário simulado serão documentados aqui.

## Testes

- **Backend:** Pest. Os testes de feature usam o banco `estudo_test` no PostgreSQL, configurado em `backend/phpunit.xml`, com `RefreshDatabase`.
- **Frontend:** Jasmine + Karma, com Chromium headless dentro do container.
- **E2E:** Playwright, na imagem oficial `mcr.microsoft.com/playwright`, contra o frontend e o backend do compose. A versão do `@playwright/test` em `e2e/package.json` deve ser a mesma da tag da imagem no `docker-compose.yml`.

## Decisões e desvios da stack

| Decisão | Justificativa |
|---|---|
| Scramble para o OpenAPI | Gera a especificação a partir das rotas, FormRequests e Resources, sem anotações duplicadas. Atende "gerada a partir do código". |
| `php artisan serve` no container do backend | Mantém o ambiente de desenvolvimento simples (um container). Em produção, troque por PHP-FPM + Nginx usando a mesma imagem base. |
| Proxy do `ng serve` para `/api` | O frontend chama `/api` na mesma origem, sem configurar CORS em desenvolvimento. |
| Fila `sync` | Ainda não há jobs. Ao criar o primeiro, troque para `redis` e adicione um worker no compose. |
| `lucascudo/laravel-pt-br-localization` (dev) | Publica as traduções pt_BR das mensagens de validação em `backend/lang/pt_BR`. O pacote só copia os arquivos; a aplicação não depende dele em execução. |
| Rate limiter `auth` por e-mail + IP | Protege cada conta contra força bruta sem bloquear todos os usuários atrás do mesmo IP. O limite vem de `AUTH_THROTTLE_PER_MINUTE`. |
| Limites de complexidade só no ESLint | O ESLint do Angular aplica os limites do DOC-DEV-069. No PHP o Pint cuida só do estilo; os limites ainda são verificados na revisão. |
