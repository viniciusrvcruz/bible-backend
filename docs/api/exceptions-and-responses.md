# Exceções e respostas da API

Contratos estáveis para clientes: sucesso via **Resources**, falhas de domínio via **`CustomException`**.

## JSON de erro

Todas as exceções de domínio que estendem `CustomException` renderizam:

```json
{
  "error": "codigo_snake_case",
  "message": "Mensagem legível"
}
```

Implementação base em `app/Exceptions/CustomException.php` — Laravel chama `render()` automaticamente se a exceção não for capturada no controller.

## Organização por contexto

Subpastas em `app/Exceptions/{Context}/` agrupam erros do **mesmo domínio** (ex.: `Version/`, `Support/`, `Chapter/`). Não é uma hierarquia única “do projeto”: cada módulo pode ter a sua.

Ao adicionar erro novo:

1. Coloque na subpasta do contexto (ou crie uma).
2. Estenda `CustomException` (ou a base já usada naquele contexto).
3. Factory methods com `error` em snake_case e HTTP status explícito — copie o estilo da classe vizinha no **mesmo** contexto.

## Quando criar uma nova exceção

- O cliente precisa distinguir **tipo de erro** (campo `error`).
- O status HTTP não pode ser 500 genérico.

Lance no **Service/Adapter/Validator**, não no controller. Testes: [testing/overview.md](../testing/overview.md).

## JSON de sucesso (Resources)

`AppServiceProvider` registra `JsonResource::withoutWrapping()` — sem `{ "data": ... }` no nível raiz.

Resources em `app/Http/Resources/`. Para endpoint novo, copie um Resource do **mesmo tipo de resposta** (model Eloquent, DTO, coleção, mensagem simples) no módulo que você está estendendo.

## Respostas que não passam por CustomException

Alguns comportamentos HTTP são tratados fora das exceções de domínio — throttle (`429`), validação do Form Request (`422`), etc. Se for regra de **uma rota ou módulo** (ex.: bloqueio prolongado de leitura de capítulo), documente no [guia desse módulo](../modules/) — não trate como padrão global da API.

## Relacionado

- [Módulos](../modules/) — regras e erros por área
- [Testes](../testing/overview.md) — assert de `error` e status
- [Arquitetura](../architecture/overview.md) — quando usar exception no checklist
