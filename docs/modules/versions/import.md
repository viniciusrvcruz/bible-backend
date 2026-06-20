# Importação de versões

Fluxo **admin** para criar traduções, importar arquivos e configurar de onde o texto dos capítulos virá depois.

Rotas (Sanctum `auth:admins`):

- `POST /api/admin/versions` — criar + importar
- `PUT /api/admin/versions/{version}` — só metadados
- `DELETE /api/admin/versions/{version}` — remoção (soft delete)

Listagem pública: `GET /api/versions` (sem auth).

## Pipeline de importação

```mermaid
flowchart LR
    A[VersionRequest] --> B[VersionImportDTOFactory]
    B --> C[VersionImportService]
    C --> D[VersionAdapterFactory]
    D --> E[adapt files → VersionDTO]
    E --> F[VersionValidator]
    F --> G[DB transaction]
    G --> H[Version::create]
    H --> I[VersionImporter]
```

### Arquivos centrais

| Arquivo | Papel |
|---------|-------|
| `app/Http/Controllers/VersionController.php` | `store`, `update`, `destroy`, `index` |
| `app/Http/Requests/VersionRequest.php` | Regras HTTP (multipart, adapter, text_source, …) |
| `app/Services/Version/Factories/VersionImportDTOFactory.php` | Request → `VersionImportDTO` |
| `app/Services/Version/VersionImportService.php` | Orquestra adapt → validar → transação |
| `app/Services/Version/Factories/VersionAdapterFactory.php` | Registry de formatos de arquivo |
| `app/Services/Version/Validators/VersionValidator.php` | Integridade estrutural e de conteúdo |
| `app/Services/Version/Importers/VersionImporter.php` | Insere books, chapters, verses, verse_references |

Core do serviço:

```19:41:app/Services/Version/VersionImportService.php
    public function import(VersionImportDTO $dto): Version
    {
        $adapter = VersionAdapterFactory::make($dto->adapterName);

        $versionData = $adapter->adapt($dto->files);

        $this->validator->validate($versionData, $dto->textSource);

        return DB::transaction(function () use ($dto, $versionData) {
            $version = Version::create([
                'abbreviation' => $dto->versionAbbreviation,
                'name' => $dto->versionName,
                'language' => $dto->language,
                'copyright' => $dto->copyright,
                'text_source' => $dto->textSource,
                'external_version_id' => $dto->externalVersionId,
                'cache_ttl' => $dto->cacheTtl,
            ]);

            $this->importer->import($versionData, $version->id);

            return $version;
        });
    }
```

## Adapters de formato (`adapter` no request)

Registrados em `VersionAdapterFactory`:

| Nome no request | Classe | Quando usar |
|-----------------|--------|-------------|
| `usfm` | `UsfmAdapter` | Arquivos `.usfm` (um arquivo por livro); texto completo + referências |
| `json_thiago_bodruk` | `JsonThiagoBodrukAdapter` | JSON com texto completo por versículo |
| `json_youversion` | `JsonYouVersionAdapter` | Estrutura livros/capítulos/versos; **texto vazio** — para `text_source=api_bible` |

Cada adapter implementa `VersionAdapterInterface::adapt(files): VersionDTO`.

### USFM (detalhe)

Pacote `app/Services/Version/Adapters/Usfm/`:

- `UsfmBookParser` / `UsfmLineParser` — leitura linha a linha
- `UsfmMarkerCleaner` + `UsfmMarkers` — remove marcadores USFM do texto
- `UsfmReferenceProcessor` — extrai referências (ex.: “cf. Jo 3.16”), gera `VerseReferenceDTO` e substitui no texto por `{{slug}}`

### DTOs da pipeline

`VersionDTO` → lista de `BookDTO` → `ChapterDTO` → `VerseDTO` + `VerseReferenceDTO`.

`VersionImportDTO` agrega metadados da versão + arquivos (`FileDTO[]`) vindos do upload.

## `text_source` na criação

| Valor | Texto nos versículos após import | Leitura de capítulo |
|-------|----------------------------------|---------------------|
| `database` | Obrigatório (validado) | `DatabaseChapterAdapter` |
| `api_bible` | Deve ser **vazio** | `ApiBibleChapterAdapter` + `external_version_id` + `cache_ttl` |

`VersionImportDTOFactory` só inclui `cache_ttl` no DTO quando a fonte **não** é `database`.

## Validação (`VersionValidator`)

Sempre verifica árvore: livros → capítulos → versículos → referências (tipos DTO corretos).

Regras adicionais para `database`:

- Texto de verso não vazio
- Referências com `slug` e `text` válidos
- Placeholders `{{slug}}` presentes no texto do verso
- Sem marcadores USFM residuais no texto

Para `api_bible`:

- Texto de verso deve permanecer vazio

Falhas lançam `VersionImportException` (HTTP 422, corpo `{ error, message }`).

## Update e delete

- **PUT**: apenas metadados (`VersionRequest` não aceita `files` nem `adapter` na atualização).
- **DELETE**: `Version::delete()` (soft delete se o model usar `SoftDeletes`).

## Persistência (`VersionImporter`)

Dentro da mesma transação do `Version::create`:

1. Books (com `abbreviation` enum)
2. Chapters
3. Verses
4. `verse_references`

## Adicionar um novo formato de arquivo

1. Implementar `VersionAdapterInterface` (retornar `VersionDTO` válido).
2. Registrar em `VersionAdapterFactory::$adapters` com nome string estável.
3. Incluir o nome em `VersionRequest` (método que lista adapters disponíveis, ex. `getAvailableAdapterNames()`).
4. Testes: copie o padrão em `tests/Unit/Services/Version/` e `tests/Feature/Version/` (ver [testing/overview.md](../../testing/overview.md)).

## Adicionar novo `text_source`

1. Enum `VersionTextSourceEnum`.
2. Regras em `VersionValidator` e `VersionRequest`.
3. Case em `ChapterSourceAdapterFactory` (ver [Capítulos](../chapters/text-sources.md)).
4. Adapter de import adequado (ex.: JSON só estrutura vs texto cheio).

## Erros e testes

- Exceção: `VersionImportException` — ver `app/Exceptions/Version/` e [api/exceptions-and-responses.md](../../api/exceptions-and-responses.md).
- Testes: [testing/overview.md](../../testing/overview.md) — espelhe `tests/Feature/Version/` e `tests/Unit/Services/Version/`.

## Relacionado

- [Capítulos e fontes de texto](../chapters/text-sources.md)
- [Autenticação](../auth/authentication.md) — admin precisa de token para importar
