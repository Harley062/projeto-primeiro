<?php
header('Content-Type: application/json');
require_once '../classes/Carrinho.php';

$carrinho = new Carrinho();
echo json_encode(['count' => $carrinho->contarItens()]);
?>
