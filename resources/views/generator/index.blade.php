<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                🎲 Gerador de Dados
            </h2>
            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit" class="text-sm text-gray-500 hover:text-gray-700">
                    Sair
                </button>
            </form>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Formulário de Geração -->
                    <div class="mb-8">
                        <form id="generatorForm" class="space-y-6">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="md:col-span-2">
                                    <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                                        Tipo de Dado
                                    </label>
                                    <select id="type" name="type" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                        <option value="email">📧 Email</option>
                                        <option value="cpf">🆔 CPF</option>
                                        <option value="cnpj">🏢 CNPJ</option>
                                        <option value="rg">📄 RG</option>
                                        <option value="password">🔒 Senha</option>
                                        <option value="phone">📱 Telefone</option>
                                        <option value="name">👤 Nome</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label for="quantity" class="block text-sm font-medium text-gray-700 mb-2">
                                        Quantidade
                                    </label>
                                    <input type="number" id="quantity" name="quantity" min="1" max="100" value="5" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>
                            
                            <div class="flex justify-center">
                                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-3 px-8 rounded-md transition duration-200 shadow-sm">
                                    🎲 Gerar Dados
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Loading -->
                    <div id="loading" class="hidden">
                        <div class="flex flex-col items-center justify-center py-12">
                            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500"></div>
                            <span class="mt-4 text-gray-600">Gerando dados...</span>
                        </div>
                    </div>
                    
                    <!-- Resultados -->
                    <div id="results" class="hidden">
                        <div class="border-t border-gray-200 pt-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-medium text-gray-900">
                                    Resultados
                                </h3>
                                <div class="flex space-x-3">
                                    <button onclick="copyResults()" class="text-blue-500 hover:text-blue-700 text-sm font-medium flex items-center">
                                        📋 Copiar Tudo
                                    </button>
                                    <button onclick="clearResults()" class="text-red-500 hover:text-red-700 text-sm font-medium flex items-center">
                                        🗑️ Limpar
                                    </button>
                                </div>
                            </div>
                            
                            <div class="bg-gray-50 rounded-lg p-4 max-h-96 overflow-y-auto">
                                <div id="resultContent" class="space-y-2">
                                    <!-- Resultados serão inseridos aqui -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Informações sobre os tipos de dados -->
            <div class="mt-8 bg-blue-50 rounded-lg p-6">
                <h3 class="text-lg font-medium text-blue-900 mb-4">ℹ️ Sobre os Dados Gerados</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-blue-800">
                    <div>
                        <p><strong>📧 Email:</strong> Endereços de email fictícios</p>
                        <p><strong>🆔 CPF:</strong> CPFs válidos matematicamente</p>
                        <p><strong>🏢 CNPJ:</strong> CNPJs válidos matematicamente</p>
                        <p><strong>📄 RG:</strong> RGs no formato padrão</p>
                    </div>
                    <div>
                        <p><strong>🔒 Senha:</strong> Senhas seguras com símbolos</p>
                        <p><strong>📱 Telefone:</strong> Números com DDDs válidos</p>
                        <p><strong>👤 Nome:</strong> Nomes brasileiros aleatórios</p>
                    </div>
                </div>
                <p class="mt-4 text-xs text-blue-600">
                    ⚠️ Todos os dados são fictícios e gerados aleatoriamente para fins de desenvolvimento e teste.
                </p>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('generatorForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            // Mostrar loading
            document.getElementById('loading').classList.remove('hidden');
            document.getElementById('results').classList.add('hidden');
            
            fetch('{{ route("generator.generate") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    type: formData.get('type'),
                    quantity: formData.get('quantity')
                })
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('loading').classList.add('hidden');
                
                if (data.success) {
                    displayResults(data.data, data.type);
                } else {
                    alert('Erro ao gerar dados. Tente novamente.');
                }
            })
            .catch(error => {
                document.getElementById('loading').classList.add('hidden');
                console.error('Erro:', error);
                alert('Erro ao gerar dados. Tente novamente.');
            });
        });

        function displayResults(data, type) {
            const resultsDiv = document.getElementById('results');
            const resultContent = document.getElementById('resultContent');
            
            resultContent.innerHTML = '';
            
            data.forEach((item, index) => {
                const div = document.createElement('div');
                div.className = 'flex justify-between items-center bg-white border border-gray-200 rounded-md px-4 py-3 hover:bg-gray-50 transition duration-200';
                div.innerHTML = `
                    <span class="text-sm text-gray-900 font-mono select-all flex-1">${item}</span>
                    <button onclick="copyToClipboard('${item.replace(/'/g, "\\'")}', this)" class="ml-4 text-blue-500 hover:text-blue-700 text-sm px-2 py-1 rounded hover:bg-blue-50 transition duration-200">
                        📋
                    </button>
                `;
                resultContent.appendChild(div);
            });
            
            resultsDiv.classList.remove('hidden');
        }

        function copyToClipboard(text, button) {
            navigator.clipboard.writeText(text).then(function() {
                const originalText = button.innerHTML;
                button.innerHTML = '✅';
                button.classList.add('text-green-500');
                
                setTimeout(() => {
                    button.innerHTML = originalText;
                    button.classList.remove('text-green-500');
                }, 1000);
            });
        }

        function copyResults() {
            const results = document.querySelectorAll('#resultContent .font-mono');
            const allText = Array.from(results).map(el => el.textContent).join('\n');
            
            navigator.clipboard.writeText(allText).then(function() {
                const button = event.target;
                const originalText = button.innerHTML;
                button.innerHTML = '✅ Copiado!';
                button.classList.add('text-green-500');
                
                setTimeout(() => {
                    button.innerHTML = originalText;
                    button.classList.remove('text-green-500');
                }, 2000);
            });
        }

        function clearResults() {
            document.getElementById('results').classList.add('hidden');
            document.getElementById('resultContent').innerHTML = '';
        }
    </script>
</x-app-layout>