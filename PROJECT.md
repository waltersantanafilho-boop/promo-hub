Apliquei as 12 alterações. Segue o PROJECT.md revisado e consolidado:# PROJECT.md — PromoHub

**Arquitetura de referência para implementação via Codex**
**Stack:** Laravel · PHP · MySQL · Blade · Bootstrap 5 · Alpine.js · Redis (futuro) · Queue · Scheduler

---

## 1. Arquitetura Geral

Monólito Laravel modular — não microserviços. Para um MVP com um único desenvolvedor/IA implementando, microserviços adicionam custo operacional (deploy, comunicação entre serviços, consistência de dados) sem benefício real neste estágio. Modularidade vem da organização interna do código, não da separação física de processos.

**Princípio central:** separar claramente três eixos que costumam se misturar em projetos desse tipo:

1. **Domínio de catálogo** (ProductGroup/Product/Offer/Category/Brand/Store) — o que existe e quanto custa
2. **Domínio de afiliados** (AffiliateProgram/AffiliateLink/AffiliateClick/Conversion) — como o clique vira dinheiro
3. **Domínio de integração** (Providers/Import/DTOs) — como dados externos entram no sistema

Cada um desses eixos deve poder evoluir sem forçar mudanças nos outros. Essa separação é a decisão arquitetural mais importante do documento e justifica boa parte das escolhas abaixo.

**Camadas (dentro do monólito):**

```
Controller  →  Service  →  Model/Repository (quando necessário)  →  DB
                  ↑
              DTOs (fronteira com dados externos)
                  ↑
              Providers (integração com marketplaces / geradores de link)
```

Jobs e Scheduler operam por cima dessa estrutura, chamando Services — nunca lógica de negócio duplicada dentro do Job.

---

## 2. Estrutura de Módulos/Domínios

Organização por domínio dentro de `app/`, não por tipo técnico.

```
app/
├── Domain/
│   ├── Catalog/          # ProductGroup, Product, Category, Brand, Offer, PriceHistory, ProductMatchCandidate
│   ├── Store/             # Store, StoreSource
│   ├── Coupon/            # Coupon, CouponScope
│   ├── Affiliate/         # AffiliateProgram, AffiliateLink, AffiliateClick, Conversion
│   ├── User/               # User, Favorite, PriceAlert
│   └── Import/             # ImportSource, ImportLog, ImportRun
│
├── Providers/
│   ├── Marketplace/
│   │   ├── Contracts/     # contrato MarketplaceProvider
│   │   ├── MercadoLivre/
│   │   ├── Amazon/
│   │   └── Shopee/
│   └── AffiliateLink/
│       ├── Contracts/     # contrato AffiliateLinkGenerator
│       ├── UrlParam/
│       └── ApiBased/
│
├── DTOs/
│   └── Marketplace/       # NormalizedProductDTO, NormalizedOfferDTO
│
├── Services/
│   ├── Catalog/            # ProductMatchingService, OfferService
│   ├── Import/              # ImportOrchestratorService
│   └── Affiliate/           # LinkGeneratorService, ClickTrackingService
│
├── Jobs/
│   ├── Import/
│   ├── PriceMonitoring/
│   └── Affiliate/
│
└── Http/
    ├── Controllers/
    │   ├── Public/
    │   └── Admin/
    └── Requests/
```

Cada domínio tem seus próprios Models, e Services que orquestram lógica entre domínios ficam em `Services/`, não dentro de um domínio específico — evita que `Catalog` dependa de `Affiliate` diretamente. `Providers/Marketplace` e `Providers/AffiliateLink` seguem o mesmo padrão de resolução por chave (ver §22 e §13).

---

## 3. Entidades Principais

**Catálogo:** ProductGroup, Product, Category, Brand, Offer, PriceHistory, ProductMatchCandidate (fila de revisão de duplicatas)
**Loja/Fonte:** Store, StoreSource
**Cupom:** Coupon (+ pivots de escopo: Offer/Product/Category)
**Afiliados:** AffiliateProgram, AffiliateLink, AffiliateClick, Conversion
**Usuário:** User, Favorite, PriceAlert
**Importação:** ImportSource, ImportLog, ImportRun
**Acesso:** Role, Permission (via pacote, não reinventar)

---

## 4. Relacionamentos (Visão Geral)

```
ProductGroup ─< Product

Category (self-referencing: parent_id)
   │
   ├─< Product >─┐
   │              │
Brand ─< Product   Offer >─ Store
                    │         │
                    │         └─< StoreSource
                    │
                    ├─< PriceHistory
                    ├─< AffiliateLink >─ AffiliateProgram
                    │        │
                    │        ├─< AffiliateClick >─< Conversion
                    │        │
                    └─< Coupon (via coupon_offer)

Product  ─< Coupon (via coupon_product)     ┐
Category ─< Coupon (via coupon_category)    ├─ mesma Coupon pode estar nos três pivots
Store    ── Coupon (store_id, cupom geral quando sem entrada nos pivots)

User ─< Favorite >─ Product
User ─< PriceAlert >─ Product

ImportSource ─< ImportRun >─< ImportLog
ImportSource ──── StoreSource (fonte que originou os dados)

Product ── ProductMatchCandidate ── Product   (par sob suspeita de duplicidade)
```

**Ponto crítico:** `Offer` pertence a `Product` (many-to-one) e a `Store` (many-to-one), mas a **origem** da oferta é rastreada via `Offer.store_source_id`, nunca via `Store` diretamente — o que resolve a exigência "Store ≠ API".

---

## 5. Modelagem de ProductGroup

`ProductGroup` representa o **modelo geral** de um produto, agrupando as variações (Products) que pertencem à mesma linha — útil para navegação e apresentação (ex: página do "iPhone 16" com seletor de armazenamento/cor), sem alterar a decisão de que cada variação continua sendo seu próprio Product, com preço e histórico independentes (ver "Variações" na análise de problemas, abaixo).

```
product_groups
- id
- name              (ex: "iPhone 16")
- slug (unique)
- category_id (FK)
- brand_id (FK)
- description (nullable)
- is_active
```

`Product.product_group_id` (nullable, FK) referencia o grupo. Nullable porque nem todo Product precisa de um grupo — variações muito específicas podem existir sozinhas, e produtos recém-importados podem aguardar agrupamento antes de receber um `product_group_id`.

**Quem cria o ProductGroup:** no cadastro manual (painel admin), o operador escolhe ou cria o grupo ao cadastrar o Product. Na importação automática, `ProductMatchingService` tenta associar a um ProductGroup existente comparando o nome-base (sem os atributos de variação) + brand + category; sem match confiável, o Product é criado sem grupo e fica disponível para agrupamento manual — mesmo espírito de fallback humano usado para duplicatas (ver §21).

---

## 6. Dados que Pertencem a Product

Product é o produto **canônico**, agnóstico de loja e agnóstico de preço.

| Campo | Motivo |
|---|---|
| `product_group_id` (nullable) | Vincula ao ProductGroup (modelo geral) — ver §5 |
| `name` | Nome canônico normalizado |
| `slug` | SEO, único |
| `description` | Descrição canônica (pode ser sobrescrita por curadoria) |
| `category_id` | FK |
| `brand_id` | FK |
| `gtin/ean` (nullable) | Identificador universal, chave forte para matching |
| `attributes` (JSON) | Variações: cor, armazenamento, tamanho, modelo |
| `canonical_image_url` | Imagem de referência (pode ser sobrescrita por oferta específica) |
| `status` (enum: active/merged/archived) | Suporta merge de duplicados sem deletar |
| `merged_into_id` (nullable, FK self) | Quando um produto é identificado como duplicata |

**Nunca pertence a Product:** preço, estoque, link, loja, cupom. Tudo isso é responsabilidade de `Offer`.

---

## 7. Dados que Pertencem a Offer

Offer é a **materialização** de um Product em uma Store, através de uma StoreSource específica.

| Campo | Motivo |
|---|---|
| `product_id` | FK |
| `store_id` | FK |
| `store_source_id` | De onde veio este dado (API/feed/manual) |
| `external_id` | ID do produto na loja de origem (SKU/ASIN/MLB) |
| `url` | Link direto (não afiliado ainda) |
| `price` | Preço atual |
| `original_price` (nullable) | Para exibir desconto |
| `currency` | Preparar multi-moeda desde já (custo zero agora, caro depois) |
| `availability` (enum: in_stock/out_of_stock/unknown) | |
| `image_url` (nullable) | Pode sobrescrever imagem canônica do Product |
| `last_checked_at` | Quando foi verificada pela última vez |
| `status` (enum: active/expired/removed) | Nunca deletar oferta, apenas marcar expirada |

**Regra obrigatória:** `UNIQUE(store_source_id, external_id)`. Essa constraint no banco garante que a mesma oferta, vinda da mesma fonte técnica, nunca gera duas linhas — uma reimportação sempre resulta em `UPDATE`, nunca em `INSERT` duplicado. É a base que torna a importação idempotente, e resolve no nível de banco boa parte do problema de "oferta duplicada por reimportação" (ver "Produtos duplicados" na análise de problemas).

**Regra de ouro:** se o dado muda por loja, é de Offer. Se é verdade sobre o objeto físico independente de onde é vendido, é de Product.

---

## 8. Modelagem de Category

```
categories
- id
- parent_id (nullable, self FK)  → hierarquia (Eletrônicos > Celulares > Smartphones)
- name
- slug (unique)
- is_active
```

Hierarquia simples via `parent_id` é suficiente para MVP. Não usar Nested Set ou Closure Table agora — adiciona complexidade que só se paga com árvores profundas e queries de árvore frequentes, o que não é o caso aqui.

---

## 9. Modelagem de Brand

```
brands
- id
- name
- slug (unique)
- logo_url (nullable)
- is_active
```

Simples de propósito. Brand não precisa de hierarquia nem metadados extras no MVP.

---

## 10. Modelagem de Store

**Store é a entidade comercial (Amazon, Mercado Livre, Shopee). StoreSource é o canal técnico pelo qual os dados dessa loja chegam.**

```
stores
- id
- name                  (ex: "Amazon Brasil")
- slug (unique)
- logo_url
- website_url
- is_active

store_sources
- id
- store_id (FK)
- type (enum: api / feed / manual / scraper)
- provider_key (nullable)  → referencia a classe Provider (ex: "amazon_paapi")
- is_active
- config (JSON, nullable)  → apenas configurações não sensíveis
```

**Regra de segurança obrigatória:** `config` armazena somente parâmetros não sensíveis (URL de feed, paginação, mapeamento de categorias, timeouts etc.). Secrets, tokens, chaves de API e credenciais **nunca** ficam em texto puro nessa coluna — nem em nenhuma outra. A forma preferida é `.env` + `config/services.php`, resolvido a partir do `provider_key` (ex: chave de API do Mercado Livre lida de `config('services.mercadolivre.token')`). Quando a credencial precisar variar por linha (ex: múltiplas contas de vendedor na mesma marketplace, caso que `.env` não resolve sozinho), usar a criptografia nativa do Eloquent (cast `encrypted`) em vez de gravar o segredo como JSON simples.

**Por quê isso importa:** amanhã a Amazon pode ter tanto integração via PA-API quanto um feed CSV manual para produtos que a API não cobre. Ambos são `store_id` = Amazon, mas `store_source_id` diferentes. `Offer.store_source_id` sempre aponta pra cá, nunca direto pro Provider.

---

## 11. Modelagem de Coupon

```
coupons
- id
- store_id (FK, nullable)     → cupom pode ser genérico de loja (não FK obrigatória)
- code
- description
- discount_type (enum: percentage / fixed)
- discount_value
- minimum_order_value (nullable)
- maximum_discount_value (nullable)
- terms (nullable)
- source_url (nullable)
- starts_at
- expires_at
- is_active
- usage_limit (nullable)

coupon_offer (pivot)
- coupon_id
- offer_id

coupon_product (pivot)
- coupon_id
- product_id

coupon_category (pivot)
- coupon_id
- category_id
```

**Sobre os novos campos:** `minimum_order_value` e `maximum_discount_value` são majoritariamente informativos neste estágio — o PromoHub não processa carrinho/checkout, então servem para exibição ("cupom válido para compras acima de R$X", "desconto máximo de R$Y"), e a validação real do valor acontece na loja de destino. `terms` guarda o regulamento resumido do cupom (texto livre) e `source_url` a página onde o cupom foi encontrado/verificado — útil para auditoria e para o usuário conferir a origem.

**Escopo do cupom (revisado):** Coupon pode se aplicar a uma Offer específica, a um Product, a uma Category, ou de forma geral a uma Store — via `store_id` sem nenhuma entrada nos pivots. A especificidade segue a ordem **Offer > Product > Category > Store**, mas essa ordem é usada para **ordenar/priorizar exibição**, não para excluir — o sistema deve retornar **todos** os cupons aplicáveis a uma Offer, não apenas o mais específico. `CouponScopeService::applicableCoupons(Offer $offer)` reúne, validando `is_active`/`expires_at`/`starts_at`/`usage_limit` em cada um:

1. Cupons vinculados diretamente à Offer (`coupon_offer`)
2. Cupons vinculados ao Product da Offer (`coupon_product`)
3. Cupons vinculados à Category do Product (`coupon_category`)
4. Cupons gerais da Store (`coupons.store_id`, sem nenhuma entrada nos três pivots acima)

O resultado é uma coleção ordenada por especificidade (1 → 4), permitindo à UI destacar o cupom mais específico como "melhor cupom" e ainda listar os demais como alternativas.

---

## 12. Modelagem de PriceHistory

```
price_histories
- id
- offer_id (FK)
- price
- recorded_at
```

Tabela append-only, nunca update/delete. Cresce indefinidamente — é esperado; índice em (`offer_id`, `recorded_at`) desde o início, arquivamento/particionamento fica como problema de infraestrutura futura, não de modelagem agora.

**Regra de escrita:**
- **Ao criar uma Offer:** grava-se imediatamente o primeiro registro de `PriceHistory` com o preço inicial — o histórico nunca começa vazio.
- **Depois disso:** um novo registro só é criado quando o preço muda (`price !== offer.price` no momento da atualização); atualizações sem mudança de preço não geram entrada nova.

Essa regra fica centralizada em `OfferService` (ver §25), tanto no método de criação quanto no de atualização — nunca replicada dentro de Providers ou Jobs, que só devem chamar o Service.

---

## 13. Modelagem de AffiliateProgram

AffiliateProgram representa o programa de afiliados de uma Store — mas nem todo programa funciona apenas adicionando um parâmetro à URL original. Redes como Awin, Rakuten ou Lomadee normalmente exigem uma chamada a API própria para gerar o link final; outros usam domínio de redirecionamento dedicado em vez de query string. Por isso o schema não fixa a estratégia — ele referencia qual implementação deve ser usada.

```
affiliate_programs
- id
- store_id (FK)
- name
- commission_rate (nullable, informativo)
- generator_key            → identifica a estratégia de geração de link (ex: "url_param", "mercadolivre_api", "awin_api")
- config (JSON, nullable)  → parâmetros não sensíveis da estratégia (ex: nome do parâmetro de tracking, domínio de redirecionamento)
- is_active
```

`generator_key` é resolvido por um resolver simples no Service Container — o mesmo padrão já usado para `StoreSource.provider_key` (ver §22) — mapeando a chave para a classe concreta de gerador. Cada gerador implementa o mesmo contrato (recebe uma Offer + AffiliateProgram, devolve a URL final), mas por dentro pode ser tão simples quanto montar uma query string ou tão complexo quanto autenticar numa API externa. `LinkGeneratorService` não conhece esse detalhe — apenas delega ao gerador correto.

A mesma regra de segurança de credenciais do `StoreSource.config` (ver §10) vale aqui: `config` guarda só parâmetros não sensíveis; tokens de API de rede de afiliados seguem para `.env`/`config/services.php` ou cast `encrypted`.

---

## 14. Modelagem de AffiliateLink

```
affiliate_links
- id
- offer_id (FK)
- affiliate_program_id (FK)
- destination_url          → URL de destino usada na geração (snapshot da Offer.url no momento)
- generated_url            → URL final com tracking/redirecionamento de afiliado
- generated_at             → quando o link foi efetivamente gerado
- short_code (nullable, unique) → se houver encurtador próprio
- is_active
```

`destination_url` registra exatamente qual URL da Offer foi usada para montar o link — a `Offer.url` pode mudar depois, e esse campo preserva o que foi de fato usado, importante para auditoria. `generated_at` é distinto de `created_at`: o registro pode existir antes do link estar pronto, especialmente com geradores baseados em API (ver §13), onde a chamada externa pode não ser instantânea — `GenerateAffiliateLinkJob` cria a linha e preenche `generated_url`/`generated_at` quando a geração é concluída.

**Decisão importante:** o link é gerado via `LinkGeneratorService` e o resultado é persistido, não recalculado a cada exibição — isso permite auditoria, cache, e desacopla geração de link de exibição de link. Regenerar é uma ação explícita (ex: quando a Offer muda de URL), não implícita a cada request.

---

## 15. Modelagem de AffiliateClick

O clique é gravado em duas etapas, para equilibrar confiabilidade com performance:

```
affiliate_clicks
- id
- affiliate_link_id (FK)
- user_id (nullable, FK)
- session_id
- clicked_at
- ip_address (hash)
- user_agent (nullable)
- referrer (nullable)
```

**Escrita síncrona (no momento do redirect):** `affiliate_link_id`, `session_id`, `clicked_at` e `user_id` (quando disponível) são gravados diretamente no controller de redirect, antes do 302 — um INSERT simples e rápido, para que o clique nunca dependa da fila para existir. Se a fila estiver indisponível ou atrasada, o clique já está salvo.

**Enriquecimento assíncrono (depois do redirect):** `ip_address` (hash), `user_agent` e `referrer` são preenchidos/normalizados por `EnrichAffiliateClickJob` logo em seguida, sem bloquear o usuário. É também o ponto onde processamento adicional futuro (ex: sinalização de bot, geolocalização) entraria, sem afetar a latência do redirect.

---

## 16. Modelagem de Conversion

Conversão é o evento de "esse clique virou venda" — normalmente reportado pela própria rede de afiliados, não detectado automaticamente pelo PromoHub.

```
conversions
- id
- affiliate_click_id (FK, nullable)  → nullable pq matching pode falhar/vir depois
- affiliate_program_id (FK)
- external_conversion_id (nullable)   → ID reportado pelo programa
- order_value (nullable)
- commission_value (nullable)
- status (enum: pending / confirmed / rejected)
- reported_at
- matched_at (nullable)
```

**MVP realista:** a maioria dos programas de afiliados não expõe API de conversão facilmente acessível no início. Modelar a tabela agora é barato; implementar o matching automático de conversão é trabalho para depois do MVP.

---

## 17. Favoritos

```
favorites
- id
- user_id (FK)
- product_id (FK)
- created_at

unique(user_id, product_id)
```

Favorito é sobre o Product canônico, não sobre uma Offer específica — o usuário quer "ficar de olho no iPhone 16", não numa oferta que pode expirar.

---

## 18. Alertas de Preço

```
price_alerts
- id
- user_id (FK)
- product_id (FK)
- target_price (nullable)     → se null, alerta em qualquer queda
- is_active
- last_notified_at (nullable) → evita spam de notificação
- created_at
```

Alerta é sobre Product porque o usuário quer saber quando **qualquer** loja atingir o preço-alvo — `CheckPriceAlertsJob` compara `target_price` contra o menor `Offer.price` ativo entre todas as lojas daquele Product.

---

## 19. Fontes/Importações

```
import_sources
- id
- store_source_id (FK)   → liga a importação à fonte técnica concreta
- name
- is_active
```

`ImportSource` existe como camada fina sobre `StoreSource` especificamente para importação: `StoreSource` descreve *como os dados daquela loja chegam em geral*; `ImportSource` é *a configuração específica de uma rotina de importação* (pode haver mais de uma rotina usando a mesma StoreSource).

Se isso parecer redundante no MVP, é aceitável começar com `ImportSource` e `StoreSource` como a mesma tabela e separar depois — **mas desenhe a Migration já prevendo a FK**, porque separar depois de haver `ImportLog` referenciando a tabela errada é o tipo de refatoração que você quer evitar (ver §28).

---

## 20. Logs e Execuções de Importação

```
import_runs
- id
- import_source_id (FK)
- started_at
- finished_at (nullable)
- status (enum: running / completed / failed / partial)
- total_processed
- total_created
- total_updated
- total_failed

import_logs
- id
- import_run_id (FK)
- level (enum: info / warning / error)
- message
- context (JSON, nullable)   → payload bruto do item que falhou, útil pra debug
- created_at
```

`ImportRun` dá a visão executiva ("a importação de ontem às 3h da Amazon processou 500 itens, 3 falharam"). `ImportLog` dá o detalhe pra investigar o item 3 que falhou. Não misturar as duas responsabilidades numa tabela só.

---

## 21. Registro de Possíveis Duplicatas (product_match_candidates)

Quando `ProductMatchingService` encontra uma correspondência **possível mas não confiável o suficiente** para merge automático (ver "Produtos duplicados" na análise de problemas, abaixo), o Product novo ainda é criado — nunca mesclado automaticamente sob incerteza — e a suspeita fica registrada para revisão humana:

```
product_match_candidates
- id
- product_id (FK)               → Product recém-criado/avaliado
- matched_product_id (FK)       → Product existente identificado como possível duplicata
- match_score (nullable)        → score de similaridade (0–1) que originou a suspeita
- reason (nullable)             → sinal que motivou a suspeita (ex: "nome similar", "mesma brand+category sem GTIN")
- import_run_id (nullable, FK)  → contexto de importação, quando aplicável
- status (enum: pending / merged / rejected)
- reviewed_by (nullable, FK → users)
- reviewed_at (nullable)
- created_at
```

`import_run_id` é nullable porque a suspeita pode surgir fora de uma importação (ex: uma rotina periódica de deduplicação revisando o catálogo já existente). Quando um admin confirma o merge no painel, a ação usa o mecanismo já existente em Product — `status = merged` + `merged_into_id` apontando para o `matched_product_id` — e o registro muda para `status = merged`. Se o admin rejeita, `status = rejected` e os dois Products continuam independentes. Essa tabela é a fila de trabalho; `Product.merged_into_id` é o resultado.

---

## 22. Arquitetura de Providers para Marketplaces/Lojas

Esta é a peça que garante "evitar acoplamento com APIs externas" (§27). Um Provider é a única classe no sistema que sabe conversar com UMA API específica de marketplace, e sua responsabilidade termina em devolver dados já normalizados via DTO.

Contrato comum a todo Provider de marketplace (`MarketplaceProvider`):
- **Buscar um produto por ID externo** → devolve um `NormalizedOfferDTO`
- **Buscar catálogo/listagem** (com filtros opcionais) → devolve múltiplos `NormalizedOfferDTO`, item a item, sem carregar tudo em memória de uma vez
- **Informar se suporta determinado recurso** (ex: histórico de preço, estoque) → permite que o orquestrador saiba o que esperar de cada marketplace sem `if` espalhado pelo código

Cada Provider concreto (MercadoLivre, Amazon, Shopee) implementa esse contrato e é o único lugar no sistema que conhece a API específica — payloads, autenticação, rate limits, paginação. Nenhum Service, Job ou Controller fora de `Providers/Marketplace/*` deve saber que a Amazon usa PA-API ou que o Mercado Livre pagina diferente da Shopee.

`ImportOrchestratorService` chama o Provider através do contrato, recebe DTOs, e passa para `ProductMatchingService`/`OfferService` — que não sabem e não precisam saber de onde veio o dado.

**Resolução do Provider certo:** via `StoreSource.provider_key`, resolvido por um binding simples no Service Container (não precisa de Factory complexa nem pacote de plugins).

---

## 23. DTOs para Normalização de Dados

DTO é a fronteira formal entre "dado bagunçado de fora" e "dado confiável de dentro".

```
NormalizedOfferDTO
- external_id
- product_name          (bruto — ainda não é o Product canônico)
- product_attributes    (JSON bruto: cor, tamanho etc conforme veio da API)
- category_hint (nullable)
- brand_hint (nullable)
- price
- original_price (nullable)
- currency
- availability
- image_url (nullable)
- url
- gtin (nullable)
```

Cada Provider transforma o payload específico da API dele em `NormalizedOfferDTO`. A partir daí, todo o resto do sistema trabalha só com o DTO — `ProductMatchingService` decide se isso é produto novo ou existente, `OfferService` decide se é oferta nova ou atualização de preço.

**Por que isso evita refatoração grande:** se a Shopee mudar o formato da API, só o `ShopeeProvider` muda. Se a estratégia de matching mudar, só `ProductMatchingService` muda.

---

## 24. Jobs Necessários

| Job | Responsabilidade | Fila sugerida |
|---|---|---|
| `ImportCatalogJob` | Dispara importação completa de uma ImportSource | `imports` |
| `ImportOfferBatchJob` | Processa um lote de DTOs (chamado pelo anterior) | `imports` |
| `CheckOfferAvailabilityJob` | Verifica se oferta ainda existe/tem estoque | `monitoring` |
| `UpdateOfferPriceJob` | Atualiza preço via `OfferService` (grava PriceHistory só se mudou) | `monitoring` |
| `CheckPriceAlertsJob` | Roda periodicamente, dispara notificação se alvo atingido | `alerts` |
| `EnrichAffiliateClickJob` | Preenche/normaliza dados do clique já registrado (ver §15) | `tracking` |
| `ExpireOffersJob` | Marca ofertas antigas sem update recente como expiradas | `maintenance` |
| `GenerateAffiliateLinkJob` | Gera/regenera link (síncrono ou via API externa) quando a Offer muda | `affiliate` |

Filas separadas permitem priorizar `tracking` (rápido, afeta UX) sobre `imports` (pesado, tolera atraso) sem trabalho extra de infraestrutura.

---

## 25. Services Necessários

| Service | Responsabilidade |
|---|---|
| `ProductMatchingService` | Decide se um DTO corresponde a Product existente, ProductGroup existente, ou é novo (ver "Produtos duplicados", abaixo) |
| `OfferService` | Cria/atualiza Offer; grava o primeiro PriceHistory na criação e novos registros só quando o preço muda |
| `CouponScopeService` | Retorna todos os cupons aplicáveis a uma Offer, ordenados por especificidade Offer > Product > Category > Store |
| `ImportOrchestratorService` | Coordena Provider → DTO → Matching → Offer, gera ImportRun/ImportLog |
| `LinkGeneratorService` | Delega ao gerador correto (via `generator_key`) para montar `generated_url` a partir de Offer + AffiliateProgram |
| `ClickTrackingService` | Lógica de correlação clique-sessão-usuário usada na escrita síncrona do clique |
| `PriceAlertService` | Verifica alvo atingido, evita notificação duplicada |
| `RankingService` | Calcula ranking de ofertas |

**Sobre RankingService:** defina desde já o critério mínimo de ranking (ex: desconto percentual + recência + cliques), mesmo que simples no MVP — centralizar num Service permite trocar o critério sem tocar em views.

---

## 26. Responsabilidades de Cada Camada

| Camada | Faz | Não faz |
|---|---|---|
| **Controller** | Recebe request, chama Service, retorna response/view | Lógica de negócio, query complexa |
| **Request (Form Request)** | Validação de entrada | Lógica de negócio |
| **Service** | Orquestra regras de negócio, transações | Detalhe de API externa, HTML/view |
| **Provider** | Fala com API externa específica, devolve DTO | Decisão de negócio (matching, pricing) |
| **DTO** | Estrutura de dados normalizada, imutável | Comportamento/lógica |
| **Model** | Relacionamentos, accessors simples, scopes | Lógica de negócio complexa, chamada a API |
| **Job** | Dispara Service de forma assíncrona/agendada | Lógica de negócio duplicada do Service |

Regra prática: se você está tentado a escrever `if` de regra de negócio dentro de um Model ou Controller, é sinal de que essa lógica pertence a um Service.

---

## 27. Como Evitar Acoplamento com APIs Externas

1. **O contrato `MarketplaceProvider`** é o único ponto de contato — nada fora de `Providers/Marketplace/*` importa SDK ou faz HTTP direto pra Amazon/ML/Shopee.
2. **DTOs são a fronteira** — uma vez que o dado vira `NormalizedOfferDTO`, ele é genérico. Matching, pricing, coupon logic nunca olham pra payload bruto.
3. **Credenciais nunca ficam em `StoreSource.config`/`AffiliateProgram.config` em texto puro** — apenas parâmetros não sensíveis vão nesses campos JSON. Segredos vivem em `.env`/`config/services.php` ou, quando precisam variar por linha, em colunas com cast `encrypted` do Eloquent (ver §10 e §13).
4. **Rate limiting e retry são responsabilidade do Provider**, não vazam pra Service ou Job (usar `Http::retry()` do Laravel dentro do Provider).
5. **Testes de Service usam DTOs mockados**, nunca precisam simular resposta de API real — o que também valida que o desacoplamento está funcionando.

---

## 28. Decisões que Precisam Ser Tomadas Agora

1. `store_source_id` em Offer desde a primeira migration, já com `UNIQUE(store_source_id, external_id)` — a constraint é o que torna a importação idempotente; adicionar depois exige limpar duplicatas acumuladas antes de conseguir aplicá-la.
2. `ImportSource` como tabela própria (ligada a StoreSource) desde o início, mesmo que hoje seja 1:1 — ver §19.
3. Currency em Offer desde já (mesmo que só `BRL` por enquanto) — evita migration + backfill quando expandir.
4. `Product.attributes` como JSON e `ProductGroup` desde a primeira migration, não como feature futura — decidir a estrutura de variações/agrupamento agora evita reescrever `ProductMatchingService` depois (ver "Variações", abaixo).
5. Enum `status` em Product e Offer desde o início (não usar `deleted_at`/soft delete como única forma de "desativar") — permite estados intermediários (`merged`, `expired`) sem overload de soft-delete.
6. `AffiliateLink.generated_url`/`destination_url` persistidos, não computados on-the-fly — decisão de §14, evita reescrever tracking depois.
7. `AffiliateProgram.generator_key` (não campos fixos de query string) desde o início — assumir que todo programa funciona só com parâmetro de URL é a premissa que quebra na primeira rede baseada em API (Awin, Rakuten) e forçaria reescrever `AffiliateLink` inteiro depois.
8. Política de credenciais definida desde o início: nada de secret em texto puro em `config` (StoreSource ou AffiliateProgram) — mudar isso depois, com segredos já gravados em texto puro no banco, significa rotacionar credenciais e migrar dados, não só alterar código.
9. Roles/Permissions via pacote consolidado (ex: Spatie Permission) desde o início do painel admin — trocar sistema de permissão depois que há Controllers/Policies escritos contra ele é retrabalho grande e evitável.

---

## 29. Ordem Ideal de Implementação

Ordenado para que cada etapa seja testável isoladamente e a próxima dependa apenas do que já existe. A partir do momento em que existe Offer, o núcleo de monetização (afiliados) pode ser construído antes de recursos de engajamento como cupons, favoritos e busca — por isso a Fase 3 foi reordenada para priorizar afiliados.

**Fase 1 — Fundação**
1. Setup Laravel + auth (Breeze ou similar) + Spatie Permission
2. Migrations: Category, Brand, Store, StoreSource
3. Migrations: ProductGroup, Product, Offer (com `UNIQUE(store_source_id, external_id)`), PriceHistory
4. Models + relacionamentos + Factories/Seeders básicos

**Fase 2 — Catálogo funcional (sem integração externa)**
5. Painel Admin: CRUD manual de ProductGroup/Product/Offer/Category/Brand/Store
6. Site público: Product Grid, Product List, Product Details
7. `OfferService` com a regra de PriceHistory (primeiro preço na criação, novo registro só quando muda)

**Fase 3 — Afiliados**
8. `AffiliateProgram` + arquitetura de geradores de link (`generator_key`) + `LinkGeneratorService`
9. `AffiliateLink` (`destination_url`/`generated_url`/`generated_at`) + rota de redirect `/go/{code}`
10. Registro síncrono mínimo de `AffiliateClick` antes do redirect + `EnrichAffiliateClickJob` assíncrono
11. `Conversion` (só estrutura — matching automático fica para depois do MVP)

**Fase 4 — Cupons e engajamento de usuário**
12. `Coupon` (com os novos campos) + pivots `coupon_offer`/`coupon_product`/`coupon_category` + `CouponScopeService`
13. Favorites + PriceAlerts (sem Job ainda, só CRUD)
14. Busca simples (Scout local ou LIKE/fulltext MySQL — sem Elasticsearch no MVP)

**Fase 5 — Integração com marketplaces**
15. Contrato `MarketplaceProvider` + DTOs
16. Primeiro Provider concreto (sugestão: Mercado Livre, API mais simples de acessar que Amazon PA-API)
17. `ProductMatchingService` + `product_match_candidates` (fila de revisão manual)
18. `ImportOrchestratorService` + ImportSource/ImportRun/ImportLog
19. `ImportCatalogJob` + Scheduler configurado
20. Repetir Provider para Amazon, depois Shopee

**Fase 6 — Automação e refino**
21. `CheckPriceAlertsJob` + notificação (mail/database notification do Laravel)
22. `ExpireOffersJob`
23. `RankingService` + homepage com ranking real
24. SEO: meta tags dinâmicas, sitemap.xml, structured data (schema.org Product)

**Fase 7 — Polish**
25. Dashboard admin com gráficos (cliques, conversões, importações) — aqui sim reaproveitar Falcon (Dashboard E-commerce)
26. Redis para cache de ranking/homepage (só entra aqui, quando há dado real pra cachear)

---

## Análise de Problemas Potenciais

### Produtos duplicados vindos de lojas diferentes

O problema real: Amazon chama "iPhone 16 128GB Preto", Mercado Livre chama "Apple iPhone 16 128 GB - Preto", e são o mesmo Product canônico.

**Estratégia de matching em camadas** (do mais confiável ao menos confiável), implementada em `ProductMatchingService`:

1. **GTIN/EAN exato** — se presente e bate, é match garantido. Prioridade máxima.
2. **`store_source_id` + `external_id` já visto antes** — garantido no nível de banco pela constraint `UNIQUE(store_source_id, external_id)` (ver §7): se a combinação já existe, é update de Offer, não criação de Product novo. Resolve a maior parte dos casos de "duplicata por reimportação".
3. **Fuzzy match de nome + brand + atributos-chave** — normalizar nome (lowercase, remover acentos/pontuação), comparar contra Products existentes da mesma Brand/Category usando similaridade de string (`similar_text`/`levenshtein`, nativos do PHP, sem lib externa no MVP). Também usado para sugerir `ProductGroup` (ver §5).
4. **Nenhum match confiável → cria Product novo** (nunca mescla sob incerteza) **e registra em `product_match_candidates`** (ver §21) com o score e o motivo da suspeita, para revisão manual no painel admin. Matching 100% automático é ilusório no MVP; o fallback humano é mais barato que perseguir um algoritmo perfeito.

### Variações (cor, armazenamento, tamanho, modelo)

Decisão para o MVP: **cada combinação de variação continua sendo um Product distinto**, não um Product "pai" com variações filhas — "iPhone 16 128GB Preto" e "iPhone 16 256GB Preto" têm preços, ofertas e histórico de preço completamente independentes, e misturar isso na mesma linha (Product → ProductVariant → Offer) só se pagaria se fosse necessário "escolher cor/tamanho antes de ver o preço", o que não é claramente necessário num comparador.

O agrupamento visual/de navegação é resolvido por `ProductGroup` (ver §5): cada Product aponta para o grupo do modelo geral ("iPhone 16"), e `attributes` (JSON) guarda `{"storage": "128GB", "color": "Preto"}` tanto para exibição quanto como sinal extra no matching. Isso já é modelagem de primeira classe, não uma feature de apresentação adiada — a página de produto pode montar o seletor de variações consultando os Products do mesmo `product_group_id`, sem precisar de tabela adicional.

### Produtos com mesmo nome, versões diferentes

Ex: "Redmi Note 12" vs "Redmi Note 12 Pro" — nomes parecidos, produtos genuinamente diferentes. O matching nunca decide sozinho com fuzzy match de nome isolado — sempre em conjunto com Brand + Category +, quando disponível, GTIN. Nome sozinho é o sinal mais fraco da lista, usado só como último critério e sempre com fallback para `product_match_candidates` em caso de incerteza (ver acima).

### Cupons aplicáveis a produto/categoria/loja

Resolvido na modelagem (§11) com pivots de três escopos (`coupon_offer`/`coupon_product`/`coupon_category`) + Store como fallback geral, seguindo a ordem de especificidade Offer > Product > Category > Store. A decisão importante aqui não é a modelagem, é que o sistema **retorna todos os cupons aplicáveis**, não apenas o mais específico — a ordem serve para ranquear/destacar, não para excluir. Documentar essa regra como comentário no próprio `CouponScopeService`, não só neste documento, evita que ela vire conhecimento implícito espalhado pelo código.

### Ofertas expiradas

`Offer.status = expired` via `ExpireOffersJob`, baseado em `last_checked_at` ultrapassando um threshold configurável (ex: 48h sem confirmação). Nunca deletar — oferta expirada ainda é histórico válido.

### Alteração de preço / histórico incorreto

Já endereçado em §12: `PriceHistory` recebe o primeiro registro na criação da Offer e, depois disso, um novo registro só quando o preço muda — nunca em toda atualização/verificação. Centralizado em `OfferService`, nunca replicado em Provider ou Job. O bug mais comum a evitar é justamente isso: job de import/verificação rodando update mesmo sem mudança de preço e gerando entradas de histórico redundantes — a comparação precisa estar num único lugar, não repetida em cada Provider.

### Links afiliados

Persistidos, não gerados on-the-fly (§14), com `destination_url` preservando a URL de origem usada e `generated_at` distinto de `created_at` — importante porque nem todo gerador é instantâneo (geradores baseados em API de rede de afiliados podem levar mais tempo que um simples template de query string, ver §13). Isso evita inconsistência entre o que foi clicado e o que é mostrado depois, e permite auditoria.

### Tracking de cliques

Escrita em duas etapas (§15): o clique mínimo é gravado de forma síncrona, antes do redirect, para nunca depender da fila — e só o enriquecimento (parsing de user agent, IP etc.) é assíncrono. `session_id` correlaciona cliques de visitante anônimo até login, se aplicável no futuro.

### SEO

Estrutura mínima pro MVP: `slug` único em Product, meta description gerável a partir de `description` truncada, sitemap.xml gerado por comando artisan agendado, structured data schema.org `Product` na página de detalhe (inclui `offers` com preço mínimo entre as Stores). Isso é Fase 6, não bloqueia nada antes.

### Crescimento futuro do catálogo

A arquitetura já assume volume desde a modelagem: `PriceHistory` append-only com índice composto, filas separadas por tipo de job, DTOs desacoplando Provider de lógica de negócio, `ImportRun`/`ImportLog` permitindo auditar importações grandes sem virar caixa preta, e `product_match_candidates` evitando que erros de matching em escala virem sujeira silenciosa no catálogo — viram fila de revisão, não bagunça invisível. Redis entra exatamente no ponto (Fase 7) em que cache de leitura (ranking, homepage) começa a importar de verdade — introduzi-lo antes seria otimização prematura sem dado real para justificar.

---

## Nota sobre o Falcon

Reaproveitamento visual (Bootstrap 5 + estrutura de markup) para: Product Grid/List/Details, Dashboard E-commerce, Add Product form, tabelas/filtros/navegação lateral do admin, telas de autenticação. Isso é decisão de **camada de apresentação (Blade + Bootstrap)**, não afeta nenhuma decisão de arquitetura/domínio acima — os componentes Pug do Falcon servem de referência de markup/CSS a ser portado manualmente para Blade, não como dependência de build (Gulp/Pug não entram no projeto). Essa etapa concentra-se nas Fases 2 e 7 da ordem de implementação.