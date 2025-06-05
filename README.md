# projeto-primeiro

# Mini ERP - Sistema de Controle de Pedidos

Sistema completo de controle de pedidos, produtos, cupons e estoque desenvolvido em PHP com MySQL e Bootstrap.

## Funcionalidades

### ✅ Produtos
- Cadastro de produtos com nome, preço e descrição
- Suporte a variações de produtos (tamanhos, cores, etc.)
- Controle de estoque por produto/variação
- Interface para atualização de produtos e estoque

### ✅ Carrinho de Compras
- Gerenciamento de carrinho em sessão
- Controle de estoque em tempo real
- Cálculo automático de frete baseado no subtotal:
  - **R$ 15,00** para subtotal entre R$ 52,00 e R$ 166,59
  - **Grátis** para subtotal acima de R$ 200,00
  - **R$ 20,00** para outros valores

### ✅ Cupons de Desconto
- Criação de cupons percentuais ou valor fixo
- Validação por período de validade
- Controle de valor mínimo do pedido
- Limite de uso configurável

### ✅ Pedidos
- Finalização de pedidos com dados do cliente
- Verificação de CEP via API ViaCEP
- Envio de email de confirmação (simulado)
- Listagem e visualização de pedidos

### ✅ Webhook
- Endpoint para atualização de status de pedidos
- Remoção automática de pedidos cancelados
- Log de requisições para debug

## Tecnologias Utilizadas

- **Backend**: PHP 7.4+ (Puro, sem frameworks)
- **Banco de Dados**: MySQL 5.7+
- **Frontend**: Bootstrap 5.3, jQuery 3.6
- **APIs**: ViaCEP para consulta de endereços

## Estrutura do Projeto

```
mini-erp/
├── config/
│   └── database.php          # Configuração do banco de dados
├── classes/
│   ├── Produto.php          # Classe para gerenciar produtos
│   ├── Carrinho.php         # Classe para gerenciar carrinho
│   ├── Cupom.php            # Classe para gerenciar cupons
│   └── Pedido.php           # Classe para gerenciar pedidos
├── includes/
│   ├── header.php           # Cabeçalho comum
│   └── footer.php           # Rodapé comum
├── ajax/
│   ├── add_to_cart.php      # Adicionar item ao carrinho
│   ├── cart_count.php       # Contador do carrinho
│   └── pedido_detalhes.php  # Detalhes do pedido
├── index.php                # Página principal (loja)
├── produtos.php             # Gerenciamento de produtos
├── carrinho.php             # Carrinho de compras
├── checkout.php             # Finalização do pedido
├── cupons.php               # Gerenciamento de cupons
├── pedidos.php              # Listagem de pedidos
├── webhook.php              # Endpoint para webhooks
├── database.sql             # Script de criação do banco
└── README.md                # Este arquivo
```

## Instalação

### 1. Pré-requisitos
- PHP 7.4 ou superior
- MySQL 5.7 ou superior
- Servidor web (Apache/Nginx)

### 2. Configuração do Banco de Dados

```sql
-- Criar o banco de dados
CREATE DATABASE mini_erp;

-- Importar o script SQL
mysql -u root -p mini_erp < database.sql
```

### 3. Configuração da Aplicação

Edite o arquivo `config/database.php` com suas credenciais:

```php
private $host = 'localhost';
private $db_name = 'mini_erp';
private $username = 'seu_usuario';
private $password = 'sua_senha';
```

### 4. Configuração do Servidor Web

Configure seu servidor web para apontar para a pasta do projeto.

**Apache (.htaccess)**:
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

## Uso

### Acessar a Aplicação
- **Loja**: `http://localhost/mini-erp/`
- **Gerenciar Produtos**: `http://localhost/mini-erp/produtos.php`
- **Gerenciar Cupons**: `http://localhost/mini-erp/cupons.php`
- **Ver Pedidos**: `http://localhost/mini-erp/pedidos.php`

### Webhook para Status de Pedidos

**Endpoint**: `POST /webhook.php`

**Payload**:
```json
{
    "id": 123,
    "status": "confirmado"
}
```

**Status aceitos**: `pendente`, `confirmado`, `enviado`, `entregue`, `cancelado`

**Comportamento**:
- Status `cancelado`: Remove o pedido do sistema
- Outros status: Atualiza o status do pedido

### Exemplo de Uso do Webhook

```bash
curl -X POST http://localhost/mini-erp/webhook.php \
  -H "Content-Type: application/json" \
  -d '{"id": 1, "status": "confirmado"}'
```

## Estrutura do Banco de Dados

### Tabelas Principais

1. **produtos**: Informações básicas dos produtos
2. **produto_variacoes**: Variações dos produtos (tamanhos, cores, etc.)
3. **estoque**: Controle de estoque por produto/variação
4. **cupons**: Cupons de desconto
5. **pedidos**: Pedidos realizados
6. **pedido_itens**: Itens de cada pedido

### Relacionamentos

- Produtos → Variações (1:N)
- Produtos → Estoque (1:N)
- Pedidos → Itens (1:N)
- Cupons → Pedidos (1:N)

## Funcionalidades Técnicas

### Arquitetura MVC
- **Models**: Classes em `classes/`
- **Views**: Arquivos PHP principais
- **Controllers**: Lógica integrada nas views (PHP puro)

### Segurança
- Prepared statements para prevenir SQL injection
- Validação de dados de entrada
- Sanitização de outputs HTML

### Performance
- Consultas otimizadas com JOINs
- Cache de sessão para carrinho
- Índices no banco de dados

## Testes

### Testar Funcionalidades Principais

1. **Produtos**:
   - Criar produto com variações
   - Atualizar estoque
   - Verificar controle de estoque no carrinho

2. **Carrinho**:
   - Adicionar produtos
   - Verificar cálculo de frete
   - Aplicar cupons

3. **Pedidos**:
   - Finalizar compra
   - Verificar CEP
   - Testar webhook

### Webhook de Teste

```bash
# Atualizar status
curl -X POST http://localhost/mini-erp/webhook.php \
  -H "Content-Type: application/json" \
  -d '{"id": 1, "status": "enviado"}'

# Cancelar pedido
curl -X POST http://localhost/mini-erp/webhook.php \
  -H "Content-Type: application/json" \
  -d '{"id": 1, "status": "cancelado"}'
```

## Logs e Debug

Os logs do webhook são gravados no error_log do PHP. Para visualizar:

```bash
tail -f /var/log/apache2/error.log
# ou
tail -f /var/log/php_errors.log
```

## Melhorias Futuras

- [ ] Autenticação de usuários
- [ ] Painel administrativo completo
- [ ] Relatórios de vendas
- [ ] Integração com gateways de pagamento
- [ ] API REST completa
- [ ] Testes automatizados

## Suporte

Para dúvidas ou problemas, verifique:
1. Logs do servidor web
2. Logs do PHP
3. Configurações do banco de dados
4. Permissões de arquivos

## Licença

Este projeto é de uso livre para fins educacionais e comerciais.
