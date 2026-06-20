# Arquitetura geral

API REST Laravel **sem Blade**. O frontend consome JSON; regra global: **controllers finos** — validação HTTP, delegação, resposta JSON/Resource.

Não existe um único “fluxo padrão” copiado de um módulo (capítulos, versões, suporte, auth usam combinações diferentes). Este guia cobre o que é **comum a todo o projeto**; detalhes de cada área ficam em [docs/modules/](../modules/).

## Camadas

```
HTTP (routes → Controller → Form Request, quando fizer sentido)
        ↓
Action e/ou Service (conforme o módulo já faz)
        ↓
Models (Eloquent) + PostgreSQL / Redis / APIs externas
        ↓
Http/Resources — formato JSON estável
```

| Camada | Pasta | Responsabilidade |
|--------|-------|------------------|
| Rotas | `routes/api.php`, `routes/web.php` | Mapeamento HTTP; OAuth em **web**, API em **api** |
| Controllers | `app/Http/Controllers/` | Entrada/saída HTTP; sem regra de negócio pesada |
| Actions | `app/Actions/` | Orquestração de **alguns** casos de uso (`execute(...)`) — **opcional** |
| Services | `app/Services/{Context}/` | Lógica de domínio por contexto (`Version/`, `Support/`, `Chapter/`, …) |
| Models | `app/Models/` | Persistência e relacionamentos |
| Resources | `app/Http/Resources/` | Serialização; sem wrapper `data` (global em `AppServiceProvider`) |
| Enums | `app/Enums/` | Valores fixos; alguns entram no **route model binding** |
| Exceptions | `app/Exceptions/` | Erros de domínio → JSON `{ error, message }` |

## Como o controller delega (escolha pelo módulo, não por “o jeito dos capítulos”)

O projeto mistura estilos válidos. Ao alterar ou criar algo, **copie o módulo mais parecido**:

| Estilo | Quando aparece | Exemplo no código |
|--------|----------------|------------------|
| **Action** (`app(SomeAction::class)->execute(...)`) | Orquestração curta antes de factory/adapter ou query focada | `BookController`, `ChapterController` |
| **Service injetado** no controller | Pipeline ou integração que o controller sempre usa | `VersionController::store` + `VersionImportService`; `SupportController` + `SupportServiceInterface` |
| **Service + Form Request → DTO** | Entrada HTTP convertida antes do domínio | `SendSupportRequest::toDTO()`, `VersionImportDTOFactory::fromRequest()` |
| **Eloquent direto no controller** | CRUD/listagem simples sem pipeline extra | `VersionController::index`, `update` |

**Actions não são obrigatórias.** Suporte não usa Action; import de versão vai direto ao Service. Não crie Action só por simetria com capítulos.

## Padrões opcionais no domínio (use só quando há variação)

### Factory + Strategy (adapters)

Aplique quando o comportamento **troca por configuração ou formato** (fonte de texto, formato de import, etc.):

- Interface em `Services/{Context}/Interfaces/`
- Implementações em `Adapters/`
- Escolha em `Factories/`

Exemplos reais: import USFM/JSON (`VersionAdapterFactory`), leitura DB vs Api.Bible (`ChapterSourceAdapterFactory`). Rotas simples (listar livros, enviar suporte) **não** precisam desse desenho.

### Interface + bind no container

Quando a implementação pode ser trocada sem mudar o controller — ex.: `SupportServiceInterface` → `OlieFlowSupportSupportService` em `AppServiceProvider`. Outro módulo pode usar classe concreta injetada sem interface; siga o que o contexto já faz.

### Validators além do Form Request

Validação estrutural grande (ex.: Bíblia inteira após parse) fica em `Services/.../Validators/`, não no Request.

Evite `if ($format === 'x')` espalhado em controllers; centralize na factory ou no service do **mesmo contexto**.

## Bootstrap: o que é global e o que é de um módulo só

**Global (vale para toda a API):**

- `CloudflareRealIp` e proxies confiáveis — IP real atrás de CDN
- `JsonResource::withoutWrapping()`
- `Model::preventLazyLoading()` fora de produção
- Binds de interface em `AppServiceProvider` (ex.: suporte)

**Específico de um módulo/rota** — não generalize como arquitetura obrigatória:

- Rate limit de capítulo (`chapter.rate_limit`, `ChapterRateLimit`) → só rota de leitura de capítulo; ver [modules/chapters/text-sources.md](../modules/chapters/text-sources.md)
- Middlewares e helpers em `app/Support/` podem ser usados por uma feature só

## Route model binding (rotas atuais da API)

| Parâmetro | Tipo resolvido |
|-----------|----------------|
| `{version}` | `App\Models\Version` |
| `{abbreviation}` | `BookAbbreviationEnum` |
| `{provider}` | `UserAuthProvider` / `AdminAuthProvider` |
| `{number}` | `int` |

Novos parâmetros: preferir enums/models com binding explícito em vez de parsing manual no controller.

## Checklist: nova funcionalidade

1. **Rota** em `api.php` ou `web.php`
2. Identificar **módulo/domínio** em `app/Services/{Context}/` (ou criar contexto novo)
3. **Controller** fino — delegar como o módulo vizinho (Action vs Service vs Eloquent)
4. **Form Request** se entrada HTTP for não trivial
5. **Factory/adapter/interface** só se houver **mais de uma implementação**
6. **Resource** (ou JSON explícito) para contrato estável
7. **Migration + Model** se persistir dados novos
8. **CustomException** se o cliente precisar de `error` tipado — ver [api/exceptions-and-responses.md](../api/exceptions-and-responses.md)
9. **Testes** — ver [testing/overview.md](../testing/overview.md); espelhar pasta do domínio em `tests/`

## Onde está o detalhe de cada área

| Necessidade | Onde ler |
|-------------|----------|
| Regra de negócio de um produto (capítulo, import, OAuth, ticket) | [docs/modules/](../modules/) — guia do módulo |
| Erros HTTP e Resources | [api/exceptions-and-responses.md](../api/exceptions-and-responses.md) |
| Como achar testes de exemplo | [testing/overview.md](../testing/overview.md) |

Índice: [README.md](../README.md).
