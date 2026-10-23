# CLAUDE.md

Repositório: Sistema de organização de projetos e tasks. Cada Projeto tem várias tasks e as tasks podem ter estados de "To do" ou "done"

## Stack

| Componente | Padrão |
|---|---|
| Sistema e containers | Linux ou WSL2, Docker e Docker Compose. Scripts em bash. |
| Backend | PHP 8.4, Laravel 12 e Composer 2, dentro do container. Laravel Sail é opcional. |
| Banco de dados | PostgreSQL 17, com um banco separado para os testes. |
| Cache e sessão | Redis 7, opcional. |
| Autenticação | Laravel Sanctum, com token enviado como Bearer. O login pode ser simplificado, mas o token e a verificação no servidor são reais. |
| Frontend | Angular 20 com Node 20 LTS, versão fixada em `.nvmrc`. Sem SSR. |
| Testes | Pest (sobre PHPUnit) no backend, testes unitários do Angular no frontend e Playwright no E2E. |
| Qualidade e CI | Pint e ESLint. GitHub Actions roda lint e testes em todo PR. |
| Documentação da API | OpenAPI (Swagger), gerada a partir do código. |
| Filas e eventos | RabbitMQ é opcional. Webhooks e eventos podem ser simulados nos testes com payloads JSON. |

Qualquer desvio desta stack, como outra versão ou outra biblioteca, precisa de justificativa no README.

## Ambiente

- Todos os comandos (composer, artisan, testes, lint, npm) rodam dentro dos containers, para manter a paridade entre desenvolvimento e produção.
- O ambiente sobe com um único comando do Docker Compose, descrito no README.
- O `.env.example` não leva segredos. O seed com dados de exemplo permite rodar a jornada do zero.

## Arquitetura do backend

- **Código organizado por domínio (DDD)**, agrupado por contexto de negócio, como devolução, ticket, NF-e, coleta, frete e reembolso.
- **Camadas:**
  1. rota;
  2. controller, um por ação, que converte a entrada e chama o caso de uso;
  3. FormRequest, que valida a requisição;
  4. ação ou serviço de aplicação, onde ficam a regra de negócio e a transação;
  5. model Eloquent.
- **Integrações externas e cenários simulados** ficam numa camada de infraestrutura. A regra de negócio fica na ação ou no serviço de aplicação, nunca no controller nem no model.
- **Invariantes também no banco:** unicidade e consistência garantidas por índices, chaves estrangeiras e restrições. Use transação e bloqueio de linha (`lockForUpdate`) onde houver concorrência.
- **Estados explícitos:** devolução, NF-e e cobrança PIX seguem máquinas de estados. As transições permitidas ficam declaradas num único lugar. Uma transição fora do previsto é recusada e o estado não muda. Cada transição grava uma linha de histórico na mesma transação.
- **Idempotência:** reenviar uma requisição, um webhook ou um evento produz um único efeito (uma solicitação, uma cobrança, um estorno). Use uma chave de idempotência: a mesma chave devolve o resultado anterior. Evento repetido ou fora de ordem não faz o estado regredir.
- **Autorização no servidor:** titularidade e permissão são verificadas no backend, recurso a recurso.
- **Valores monetários** são inteiros em centavos (`bigint`), nunca float.

## Comunicação entre serviços

- **Frontend e backend:** REST com JSON. O usuário se autentica e recebe um token, enviado como `Authorization: Bearer` em cada chamada.
- **Entre serviços, de forma síncrona:** REST, com um token de serviço separado do token do usuário.
- **Entre serviços, de forma assíncrona:** eventos com o envelope padrão:

  ```json
  {
    "id": "...",
    "version": "...",
    "data": {},
    "metadata": { "type": "...", "source": "...", "occurred_at": "..." }
  }
  ```

- **Sistemas externos** não usam emuladores. As respostas e os avisos desses sistemas entram como cenários: arquivos JSON, dados gerados ou valores fixos no código.
- **Documentação no README:** os contratos dos eventos e a origem de cada cenário.

## Frontend

- Componentes organizados por página ou funcionalidade.
- Serviços para as chamadas HTTP e um interceptor que injeta o token.
- Toda tela trata os estados de carregamento, vazio e erro.

## Testes

- **Unidade:** testes de unidade no backend e testes unitários do Angular no frontend.
- **Feature:** a API de ponta a ponta, com o banco de teste real (PostgreSQL), não SQLite nem mocks do banco.
- **E2E com Playwright:** é a camada de regressão da integração.
- **Casos obrigatórios:** pagamento, estorno, webhooks e eventos externos têm testes de idempotência (o reenvio gera um único efeito), de transação (rollback sem efeito parcial) e de falha parcial.
- **Máquinas de estado:** cada uma tem testes das transições válidas e da recusa das inválidas.
- **Prazos:** vêm de parâmetro configurável. Os testes controlam o relógio, por exemplo com `travelTo`.

## Qualidade

Rode o lint antes de concluir qualquer alteração: Pint no PHP e ESLint no Angular.

Limites de complexidade e tamanho (DOC-DEV-069):

| Métrica | Limite |
|---|---|
| Complexidade ciclomática | até 10 |
| Aninhamento | até 3 níveis |
| Parâmetros por função | até 5 |
| Tamanho da função | até 50 linhas |
| Tamanho da classe | até 300 linhas |

Segredos ficam fora do código, dos logs e dos commits.

## Git e CI

- A `main` é protegida: o código entra só por PR aprovado e com CI verde.
- Cada etapa ou requisito funcional vira um PR pequeno, com a descrição do que foi feito e de como foi testado.
- Os commits são pequenos e descritivos.
- A pipeline do GitHub Actions roda lint e testes em todo PR.

## Histórico de comandos

Cada pedido feito ao Claude neste repositório é registrado em `HISTORICO.md`, na raiz, para gerar relatórios no futuro.

- **Quando registrar:** ao terminar cada pedido, inclusive os que só respondem a uma pergunta ou que falharam.
- **Onde:** acrescente a entrada no fim de `HISTORICO.md`. Nunca apague nem reescreva entradas anteriores. Se o arquivo não existir, crie-o com o cabeçalho `# Histórico de comandos`.
- **Formato de cada entrada:**

  ```markdown
  ## AAAA-MM-DD HH:MM — <título curto do pedido>

  - **Pedido:** o que foi pedido, resumido em uma ou duas frases.
  - **O que foi feito:** as ações executadas e as decisões tomadas.
  - **Arquivos alterados:** a lista de caminhos, ou "nenhum".
  - **Resultado:** concluído, parcial ou não concluído, com testes e lint rodados e o desfecho deles.
  - **Pendências:** o que ficou para depois, ou "nenhuma".
  ```

- **Segredos:** tokens, senhas e dados pessoais não entram no histórico.
