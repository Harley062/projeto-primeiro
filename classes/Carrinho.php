<?php
session_start();

class Carrinho {
    
    public function __construct() {
        if (!isset($_SESSION['carrinho'])) {
            $_SESSION['carrinho'] = [];
        }
    }

    public function adicionarItem($produto_id, $variacao_id, $quantidade, $preco_unitario, $nome_produto, $nome_variacao) {
        $key = $produto_id . '_' . $variacao_id;
        
        if (isset($_SESSION['carrinho'][$key])) {
            $_SESSION['carrinho'][$key]['quantidade'] += $quantidade;
        } else {
            $_SESSION['carrinho'][$key] = [
                'produto_id' => $produto_id,
                'variacao_id' => $variacao_id,
                'quantidade' => $quantidade,
                'preco_unitario' => $preco_unitario,
                'nome_produto' => $nome_produto,
                'nome_variacao' => $nome_variacao
            ];
        }
    }

    public function removerItem($key) {
        if (isset($_SESSION['carrinho'][$key])) {
            unset($_SESSION['carrinho'][$key]);
        }
    }

    public function atualizarQuantidade($key, $quantidade) {
        if (isset($_SESSION['carrinho'][$key])) {
            if ($quantidade <= 0) {
                $this->removerItem($key);
            } else {
                $_SESSION['carrinho'][$key]['quantidade'] = $quantidade;
            }
        }
    }

    public function obterItens() {
        return $_SESSION['carrinho'] ?? [];
    }

    public function calcularSubtotal() {
        $subtotal = 0;
        foreach ($this->obterItens() as $item) {
            $subtotal += $item['preco_unitario'] * $item['quantidade'];
        }
        return $subtotal;
    }

    public function calcularFrete($subtotal) {
        if ($subtotal >= 200.00) {
            return 0; // Frete grátis
        } elseif ($subtotal >= 52.00 && $subtotal <= 166.59) {
            return 15.00;
        } else {
            return 20.00;
        }
    }

    public function limpar() {
        $_SESSION['carrinho'] = [];
    }

    public function contarItens() {
        $total = 0;
        foreach ($this->obterItens() as $item) {
            $total += $item['quantidade'];
        }
        return $total;
    }
}
?>
