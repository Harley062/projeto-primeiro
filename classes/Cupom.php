<?php
require_once 'config/database.php';

class Cupom {
    private $conn;
    private $table_name = "cupons";

    public $id;
    public $codigo;
    public $tipo;
    public $valor;
    public $valor_minimo;
    public $data_inicio;
    public $data_fim;
    public $limite_uso;
    public $usado;
    public $ativo;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function validarCupom($codigo, $subtotal) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE codigo = :codigo 
                  AND ativo = 1 
                  AND data_inicio <= CURDATE() 
                  AND data_fim >= CURDATE()
                  AND :subtotal >= valor_minimo
                  AND (limite_uso IS NULL OR usado < limite_uso)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":codigo", $codigo);
        $stmt->bindParam(":subtotal", $subtotal);
        $stmt->execute();
        
        return $stmt->fetch();
    }

    public function calcularDesconto($cupom, $subtotal) {
        if (!$cupom) return 0;
        
        if ($cupom['tipo'] == 'percentual') {
            return ($subtotal * $cupom['valor']) / 100;
        } else {
            return $cupom['valor'];
        }
    }

    public function usarCupom($id) {
        $query = "UPDATE " . $this->table_name . " 
                  SET usado = usado + 1 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        
        return $stmt->execute();
    }

    public function listar() {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE ativo = 1 
                  ORDER BY created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    public function criar() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET codigo=:codigo, tipo=:tipo, valor=:valor, 
                      valor_minimo=:valor_minimo, data_inicio=:data_inicio, 
                      data_fim=:data_fim, limite_uso=:limite_uso";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":codigo", $this->codigo);
        $stmt->bindParam(":tipo", $this->tipo);
        $stmt->bindParam(":valor", $this->valor);
        $stmt->bindParam(":valor_minimo", $this->valor_minimo);
        $stmt->bindParam(":data_inicio", $this->data_inicio);
        $stmt->bindParam(":data_fim", $this->data_fim);
        $stmt->bindParam(":limite_uso", $this->limite_uso);
        
        return $stmt->execute();
    }
}
?>
