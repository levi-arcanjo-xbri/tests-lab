# Histórico de comandos

## 2026-10-23 08:37 — Esqueleto e configuração inicial do projeto

- **Pedido:** criar o esqueleto e a configuração inicial do projeto com a stack do CLAUDE.md, pronto para iniciar o desenvolvimento das features.
- **O que foi feito:**
  - Docker Compose com backend (PHP 8.4), frontend (Node 20 + Chromium), PostgreSQL 17 (bancos `estudo` e `estudo_test`), Redis 7 e serviço `e2e` (Playwright) sob o profile `e2e`. Sobe com `docker compose up -d`; os entrypoints instalam dependências, criam o `.env`, geram a chave e rodam migrations com seed. Healthchecks em todos os serviços. Portas padrão 8080/4300/5442/6389, porque 8000/4200/5432 já estavam em uso por outro projeto.
  - Backend Laravel 12 criado no container, sem assets de frontend. Organização por domínio em `app/Domain` (`Auth` implementado; `Project` e `Task` com as pastas das camadas) e `app/Infrastructure`. Sanctum com login, logout e `me` (controllers invocáveis, FormRequest, ação, Resource). Exceções em JSON para `api/*`. Seed idempotente com usuário demo.
  - Pest no lugar do PHPUnit, com testes de feature no PostgreSQL de teste real. Scramble para o OpenAPI, com esquema Bearer. Pint com preset laravel.
  - Frontend Angular 20 sem SSR: `core` (AuthService, interceptor Bearer que trata 401, guards), `features` (login e home, com estados de carregamento e erro), proxy `/api` para o backend. ESLint (angular-eslint) com os limites do DOC-DEV-069. Karma com Chromium headless.
  - E2E com Playwright 1.64.1 na imagem oficial, cobrindo o login com sucesso, o logout e as credenciais inválidas.
  - `scripts/check.sh` (lint e testes por componente), `scripts/artisan.sh`, workflow do GitHub Actions (backend, frontend e e2e via compose), README com instruções, estrutura e justificativas de desvio. `git init` na branch `main`, sem commit.
- **Arquivos alterados:** `docker-compose.yml`, `.env.example`, `.gitignore`, `.nvmrc`, `README.md`, `HISTORICO.md`, `docker/**`, `scripts/**`, `.github/workflows/ci.yml`, `backend/**` (projeto Laravel novo), `frontend/**` (projeto Angular novo), `e2e/**`.
- **Resultado:** concluído. `scripts/check.sh` passou por completo: Pint ok, Pest 8 testes ok, ESLint ok, Angular 7 testes ok, Playwright 2 testes ok.
- **Pendências:** primeiro commit e configuração do remoto e da proteção da `main` no GitHub; o CI ainda não rodou no GitHub. Limites de complexidade no PHP não têm verificação automática. Fila em `sync` até existir o primeiro job.

## 2026-10-23 10:08 — Implementação completa de projetos e tasks

- **Pedido:** implementar o sistema completo (endpoints, páginas e fluxos) de forma usável, como base de estudo, com comentários explicando arquivos e trechos de código, e design simples e moderno.
- **O que foi feito:**
  - Backend: migrations de `projects`, `tasks`, `task_status_histories` e `idempotency_keys`, com FKs, índices únicos e CHECKs (estado e coerência de `completed_at`). Contextos `Project` e `Task` com controllers invocáveis, FormRequests, Actions, Resources e `ProjectPolicy` (404 para recurso de outro usuário). Máquina de estados `TaskStateMachine` (todo ⇄ done), transição com `lockForUpdate` e histórico na mesma transação, e exceção de domínio com resposta 409. Cadastro de usuário. Middleware de idempotência em `app/Infrastructure/Idempotency`, com limpeza por `model:prune` agendado. Rate limiter `auth` por e-mail + IP, configurável. Traduções pt_BR das validações. Seed com projetos e tasks de exemplo criados pelas Actions.
  - Frontend: design com tokens CSS (tema claro e escuro) e fonte Inter. Páginas de login, cadastro, lista de projetos (busca com RxJS, skeleton, vazio e erro) e detalhe do projeto (progresso, edição e exclusão, nova task, abas de filtro). Componente de task (concluir/reabrir via `allowed_transitions`, edição inline, histórico e exclusão). Toasts, ícones SVG, shell autenticado, `Idempotency-Key` gerada no cliente e locale pt-BR.
  - Bugs encontrados e corrigidos durante a validação: `crypto.randomUUID` indisponível fora de contexto seguro (fallback com `getRandomValues`); formulário de nova task recarregando a página por falta de `[formGroup]`; coluna `jsonb` reordenando a resposta guardada (trocada por `json`).
  - Comentários didáticos em todos os arquivos novos e nos de Auth. README reescrito com funcionalidades, caminho de uma requisição, tabela de invariantes, API completa, códigos de erro, idempotência e roteiro de estudo.
- **Arquivos alterados:** `README.md`, `HISTORICO.md`, `backend/app/**`, `backend/bootstrap/app.php`, `backend/config/{auth,idempotency,scramble}.php`, `backend/routes/{api,console}.php`, `backend/database/**`, `backend/lang/**`, `backend/tests/**`, `backend/.env.example`, `backend/composer.{json,lock}`, `frontend/src/**`, `e2e/tests/**`.
- **Resultado:** concluído. `scripts/check.sh` passou por completo: Pint ok, Pest 47 testes ok, ESLint ok, Angular 25 testes ok, Playwright 10 testes ok. Também conferi o visual por screenshots (claro, escuro e mobile).
- **Pendências:** commits e PRs não foram feitos (o repositório segue sem commit). Os limites de complexidade no PHP continuam sem verificação automática (conferidos manualmente). Exclusões usam o `confirm()` nativo do navegador.

## 2026-10-23 10:12 — Commits organizados por etapa

- **Pedido:** organizar o trabalho em commits por etapa.
- **O que foi feito:** commit inicial na `main` só com `CLAUDE.md` e `.gitignore`, porque a `main` é protegida e o código deve entrar por PR. O restante foi para a branch `feat/estrutura-e-funcionalidades`, em cinco commits: infraestrutura (Docker, scripts, CI), backend, frontend, E2E e documentação. Cada commit agrupa uma área inteira para que nenhum estado intermediário fique quebrado.
- **Arquivos alterados:** `HISTORICO.md` (os demais só foram versionados).
- **Resultado:** concluído. Árvore de trabalho limpa. Sem testes novos; a última execução completa do `scripts/check.sh` estava verde.
- **Pendências:** criar o repositório remoto no GitHub, configurar a proteção da `main`, fazer o push e abrir o PR.

## 2026-10-23 — Aula de Laravel a partir do projeto

- **Pedido:** ensinar Laravel usando a estrutura deste projeto.
- **O que foi feito:** li as rotas, o `bootstrap/app.php` e os arquivos do contexto Task (controller, FormRequest, Action, máquina de estados, model, exceção), da criação de projeto (controller, request, action, resource, policy), a migration de tasks, a factory, o middleware de idempotência e os testes de transição. Montei uma aula no chat que segue o caminho de uma requisição (`PATCH /api/projects/{project}/tasks/{task}/status`) camada por camada, com exercícios práticos no fim.
- **Arquivos alterados:** `HISTORICO.md`.
- **Resultado:** concluído. Nenhum código alterado; sem lint ou testes a rodar.
- **Pendências:** nenhuma. Os exercícios ficam para o usuário.

## 2026-10-23 — Explicação do operador `::` no PHP

- **Pedido:** explicar o que significa `::` no PHP, a partir de `protected $model = Task::class;` na `TaskFactory`.
- **O que foi feito:** expliquei no chat o operador de resolução de escopo (`::`): `Classe::class`, métodos estáticos (`Task::factory()`), constantes e cases de enum (`TaskStatus::Todo`), propriedades estáticas e `self::`, `static::` e `parent::`, com exemplos do próprio projeto e a diferença para `->`.
- **Arquivos alterados:** `HISTORICO.md`.
- **Resultado:** concluído. Nenhum código alterado; sem lint ou testes a rodar.
- **Pendências:** nenhuma.

## 2026-10-23 — Origem da função `fake()`

- **Pedido:** explicar de onde vem `fake()` na `TaskFactory`, já que não há `use` para ela.
- **O que foi feito:** localizei a definição em `vendor/laravel/framework/src/Illuminate/Foundation/helpers.php` e expliquei no chat: é uma função global carregada pelo autoload `files` do Composer, e o PHP procura funções não qualificadas primeiro no namespace atual e depois no global, por isso não precisa de `use`. Mostrei também o que ela devolve (um `Faker\Generator` singleton por locale).
- **Arquivos alterados:** `HISTORICO.md`.
- **Resultado:** concluído. Nenhum código alterado; sem lint ou testes a rodar.
- **Pendências:** nenhuma.
