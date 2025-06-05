    </div>

    <footer class="bg-light mt-5 py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>Mini ERP</h5>
                    <p class="text-muted">Sistema de controle de pedidos, produtos, cupons e estoque.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="text-muted">
                        <i class="fas fa-code"></i> Desenvolvido com PHP e Bootstrap
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function updateCartCount() {
            $.get('ajax/cart_count.php', function(data) {
                $('#cart-count').text(data.count);
            });
        }

        function buscarCEP(cep) {
            cep = cep.replace(/\D/g, '');
            
            if (cep.length === 8) {
                $.get('https://viacep.com.br/ws/' + cep + '/json/', function(data) {
                    if (!data.erro) {
                        $('#endereco_logradouro').val(data.logradouro);
                        $('#endereco_bairro').val(data.bairro);
                        $('#endereco_cidade').val(data.localidade);
                        $('#endereco_uf').val(data.uf);
                        $('#endereco_numero').focus();
                    } else {
                        alert('CEP não encontrado!');
                    }
                }).fail(function() {
                    alert('Erro ao buscar CEP!');
                });
            }
        }

        function mascaraCEP(input) {
            let value = input.value.replace(/\D/g, '');
            value = value.replace(/(\d{5})(\d)/, '$1-$2');
            input.value = value;
        }

        $(document).ready(function() {
            updateCartCount();
        });
    </script>
</body>
</html>
