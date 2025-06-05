<?php
require_once 'config/database.php';

class Pedido {
    private $conn;
    private $table_name = "pedidos";

    public $id;
    public $subtotal;
    public $desconto;
    public $frete;
    public $total;
    public $cupom_id;
    public $status;
    public $cliente_nome;
    public $cliente_email;
    public $cliente_telefone;
    public $endereco_cep;
    public $endereco_logradouro;
    public $endereco_numero;
    public $endereco_complemento;
    public $endereco_bairro;
    public $endereco_cidade;
    public $endereco_uf;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function criar() {
        try {
            $this->conn->beginTransaction();
            
            $query = "INSERT INTO " . $this->table_name . " 
                      SET subtotal=:subtotal, desconto=:desconto, frete=:frete, 
                          total=:total, cupom_id=:cupom_id, status=:status,
                          cliente_nome=:cliente_nome, cliente_email=:cliente_email, 
                          cliente_telefone=:cliente_telefone, endereco_cep=:endereco_cep,
                          endereco_logradouro=:endereco_logradouro, endereco_numero=:endereco_numero,
                          endereco_complemento=:endereco_complemento, endereco_bairro=:endereco_bairro,
                          endereco_cidade=:endereco_cidade, endereco_uf=:endereco_uf";
            
            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(":subtotal", $this->subtotal);
            $stmt->bindParam(":desconto", $this->desconto);
            $stmt->bindParam(":frete", $this->frete);
            $stmt->bindParam(":total", $this->total);
            $stmt->bindParam(":cupom_id", $this->cupom_id);
            $stmt->bindParam(":status", $this->status);
            $stmt->bindParam(":cliente_nome", $this->cliente_nome);
            $stmt->bindParam(":cliente_email", $this->cliente_email);
            $stmt->bindParam(":cliente_telefone", $this->cliente_telefone);
            $stmt->bindParam(":endereco_cep", $this->endereco_cep);
            $stmt->bindParam(":endereco_logradouro", $this->endereco_logradouro);
            $stmt->bindParam(":endereco_numero", $this->endereco_numero);
            $stmt->bindParam(":endereco_complemento", $this->endereco_complemento);
            $stmt->bindParam(":endereco_bairro", $this->endereco_bairro);
            $stmt->bindParam(":endereco_cidade", $this->endereco_cidade);
            $stmt->bindParam(":endereco_uf", $this->endereco_uf);
            
            if($stmt->execute()) {
                $pedido_id = $this->conn->lastInsertId();
                $this->conn->commit();
                return $pedido_id;
            }
            
            $this->conn->rollback();
            return false;
            
        } catch(Exception $e) {
            $this->conn->rollback();
            return false;
        }
    }

    public function adicionarItem($pedido_id, $produto_id, $variacao_id, $quantidade, $preco_unitario) {
        $subtotal = $quantidade * $preco_unitario;
        
        $query = "INSERT INTO pedido_itens 
                  SET pedido_id=:pedido_id, produto_id=:produto_id, 
                      variacao_id=:variacao_id, quantidade=:quantidade, 
                      preco_unitario=:preco_unitario, subtotal=:subtotal";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":pedido_id", $pedido_id);
        $stmt->bindParam(":produto_id", $produto_id);
        $stmt->bindParam(":variacao_id", $variacao_id);
        $stmt->bindParam(":quantidade", $quantidade);
        $stmt->bindParam(":preco_unitario", $preco_unitario);
        $stmt->bindParam(":subtotal", $subtotal);
        
        return $stmt->execute();
    }

    public function buscarPorId($id) {
        $query = "SELECT p.*, c.codigo as cupom_codigo
                  FROM " . $this->table_name . " p
                  LEFT JOIN cupons c ON p.cupom_id = c.id
                  WHERE p.id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        
        return $stmt->fetch();
    }

    public function listar($limit = 50) {
        $query = "SELECT p.*, c.codigo as cupom_codigo
                  FROM " . $this->table_name . " p
                  LEFT JOIN cupons c ON p.cupom_id = c.id
                  ORDER BY p.created_at DESC
                  LIMIT " . $limit;
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    public function atualizarStatus($id, $status) {
        $query = "UPDATE " . $this->table_name . " 
                  SET status = :status 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":id", $id);
        
        return $stmt->execute();
    }

    public function excluir($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        
        return $stmt->execute();
    }
}
?>
