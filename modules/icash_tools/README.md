# icash_tools - Roteiro do Modulo (Propostas)

## Objetivo deste roteiro
Documento de referencia rapida para manutencao da tela de propostas (`listar_propostas`) e das acoes de gerenciamento (`Gerenciar_propostas`), incluindo a regra de cache de assets.

## Arquivos-chave
- `modules/icash_tools/controllers/Listar_propostas.php`
- `modules/icash_tools/controllers/Gerenciar_propostas.php`
- `modules/icash_tools/views/listar_propostas.php`
- `modules/icash_tools/assets/js/propostas_funcoes_tela_corbans.js`
- `modules/icash_tools/views/templates/template-edit-proposal.php`

## Fluxo principal da tela `listar_propostas`
1. Entrada principal:
   `Listar_propostas::index()` monta a consulta, aplica filtros e prepara os dados da tabela.
2. Filtros da tela:
   - Data inicial/final via GET (`data_inicial`, `data_final`)
   - Status via GET (`status`)
3. Regras de visibilidade:
   - Admin/supervisor: visao ampla
   - Demais perfis: filtro por permissoes (`view_employee`, `view_own`, `view_network`)
4. Enriquecimento dos dados:
   - Leitura de campos customizados de proposta
   - Montagem de botoes/acoes (detalhes, contrato, documentos, etc.)
5. Renderizacao:
   `Listar_propostas::index()` envia `$propostas` para `views/listar_propostas.php`.

## Fluxo de acoes de gerenciamento (`Gerenciar_propostas`)
1. Atualizar status:
   - Endpoint: `Gerenciar_propostas::update_status()`
   - Atualiza status da proposta e campo customizado "Etapa" (field id 64)
   - Registra historico via hook `before_history_register`
2. Editar proposta:
   - Endpoint: `Gerenciar_propostas::onUpdateProposal()`
   - Origem do submit: `views/templates/template-edit-proposal.php`
   - Atualiza campos da proposta, itens e custom fields
   - Sincroniza nome/CPF do cliente quando `rel_type = customer`
   - Registra no historico os campos alterados
3. Atualizacao de status recentes (polling):
   - Endpoint: `Gerenciar_propostas::get_status_atualizados()`
   - Retorna propostas atualizadas recentemente em JSON

## Regra de cache (IMPORTANTE)
### Contexto
Na tela `views/listar_propostas.php`, os assets CSS/JS usam versao na query string para forcar atualizacao no navegador.

### Onde fica
- Variavel de versao: `views/listar_propostas.php`, linha 4
- CSS com versao:
  `assets/css/icash-tools-proposals-styles.css?v=$version`
- JS com versao:
  `assets/js/propostas_funcoes_tela_corbans.js?ver=$version`

### Procedimento padrao quando alterar CSS/JS da tela
1. Alterar os arquivos de estilo/script necessarios.
2. Incrementar manualmente o `$version` em `views/listar_propostas.php` (linha 4).
3. Recarregar a pagina e validar no navegador (hard refresh se preciso).
4. Confirmar que o asset foi servido com a nova query string.

## Check rapido de troubleshooting
1. Alteracao de front nao apareceu?
   - Verificar se o `$version` foi incrementado.
2. Endpoint de acao falhou?
   - Conferir permissao do usuario (`staff_can`) e rota chamada no JS.
3. Historico nao registrou?
   - Validar disparo do hook `before_history_register`.

## Observacao de manutencao
Como melhoria futura, o versionamento pode ser automatizado com `filemtime()` para reduzir erro humano em cache busting.
