<?php
require_once 'config/database.php';

class Produto {
    private $conn;
    private $table_name = "produtos";

    public $id;
    public $nome;
    public $preco;
    public $descricao;
    public $ativo;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function listar() {
        $query = "SELECT p.*, 
                         GROUP_CONCAT(CONCAT(pv.id, ':', pv.nome, ':', pv.valor_adicional) SEPARATOR '|') as variacoes,
                         GROUP_CONCAT(CONCAT(e.variacao_id, ':', e.quantidade) SEPARATOR '|') as estoque
                  FROM " . $this->table_name . " p
                  LEFT JOIN produto_variacoes pv ON p.id = pv.produto_id AND pv.ativo = 1
                  LEFT JOIN estoque e ON p.id = e.produto_id
                  WHERE p.ativo = 1
                  GROUP BY p.id
                  ORDER BY p.nome";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    public function buscarPorId($id) {
        $query = "SELECT p.*, 
                         GROUP_CONCAT(CONCAT(pv.id, ':', pv.nome, ':', pv.valor_adicional) SEPARATOR '|') as variacoes,
                         GROUP_CONCAT(CONCAT(e.variacao_id, ':', e.quantidade) SEPARATOR '|') as estoque
                  FROM " . $this->table_name . " p
                  LEFT JOIN produto_variacoes pv ON p.id = pv.produto_id AND pv.ativo = 1
                  LEFT JOIN estoque e ON p.id = e.produto_id
                  WHERE p.id = ? AND p.ativo = 1
                  GROUP BY p.id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        
        return $stmt->fetch();
    }

    public function criar() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET nome=:nome, preco=:preco, descricao=:descricao";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":nome", $this->nome);
        $stmt->bindParam(":preco", $this->preco);
        $stmt->bindParam(":descricao", $this->descricao);
        
        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        
        return false;
    }

    public function atualizar() {
        $query = "UPDATE " . $this->table_name . " 
                  SET nome=:nome, preco=:preco, descricao=:descricao
                  WHERE id=:id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":nome", $this->nome);
        $stmt->bindParam(":preco", $this->preco);
        $stmt->bindParam(":descricao", $this->descricao);
        $stmt->bindParam(":id", $this->id);
        
        return $stmt->execute();
    }

    public function adicionarVariacao($produto_id, $nome, $valor_adicional = 0) {
        $query = "INSERT INTO produto_variacoes 
                  SET produto_id=:produto_id, nome=:nome, valor_adicional=:valor_adicional";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":produto_id", $produto_id);
        $stmt->bindParam(":nome", $nome);
        $stmt->bindParam(":valor_adicional", $valor_adicional);
        
        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        
        return false;
    }

    public function atualizarEstoque($produto_id, $variacao_id, $quantidade) {
        $query = "INSERT INTO estoque (produto_id, variacao_id, quantidade) 
                  VALUES (:produto_id, :variacao_id, :quantidade)
                  ON DUPLICATE KEY UPDATE quantidade = :quantidade2";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":produto_id", $produto_id);
        $stmt->bindParam(":variacao_id", $variacao_id);
        $stmt->bindParam(":quantidade", $quantidade);
        $stmt->bindParam(":quantidade2", $quantidade);
        
        return $stmt->execute();
    }

    public function verificarEstoque($produto_id, $variacao_id, $quantidade_desejada) {
        $query = "SELECT quantidade FROM estoque 
                  WHERE produto_id = :produto_id AND variacao_id = :variacao_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":produto_id", $produto_id);
        $stmt->bindParam(":variacao_id", $variacao_id);
        $stmt->execute();
        
        $estoque = $stmt->fetch();
        
        return $estoque && $estoque['quantidade'] >= $quantidade_desejada;
    }

    public function reduzirEstoque($produto_id, $variacao_id, $quantidade) {
        $query = "UPDATE estoque 
                  SET quantidade = quantidade - :quantidade 
                  WHERE produto_id = :produto_id AND variacao_id = :variacao_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":quantidade", $quantidade);
        $stmt->bindParam(":produto_id", $produto_id);
        $stmt->bindParam(":variacao_id", $variacao_id);
        
        return $stmt->execute();
    }
}
?>
