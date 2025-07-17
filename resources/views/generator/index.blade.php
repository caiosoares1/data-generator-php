<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Gerador de Dados') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <!-- Formulário de Geração -->
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Selecione o tipo de dado para gerar:</h3>
                        
                        <form id="generatorForm" class="space-y-4">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="type" class="block text-sm font-medium text-gray-700">Tipo de Dado</label>
                                    <select id="type" name="type" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
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
                                    <label for="quantity" class="block text-sm font-medium text-gray-700">Quantidade</label>
                                    <input type="number" id="quantity" name="quantity" min="1" max="100" value="1" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                            </div>
                            
                            <div class="flex justify-start">
                                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition duration-200">
                                    🎲 Gerar Dados
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Área de Loading -->
                    <div id="loading" class="hidden">
                        <div class="flex items-center justify-center py-4">
                            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                            <span class="ml-2">Gerando dados...</span>
                        </div>
                    </div>
                    
                    <!-- Resultados -->
                    <div id="results" class="hidden">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Resultados:</h3>
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm font-medium text-gray-700">Dados gerados:</span>
                                <div class="space-x-2">
                                    <button onclick="copyResults()" class="text-blue-500 hover:text-blue-700 text-sm font-medium">
                                        📋 Copiar Tudo
                                    </button>
                                    <button onclick="clearResults()" class="text-red-500 hover:text-red-700 text-sm font-medium">
                                        🗑️ Limpar
                                    </button>
                                </div>
                            </div>
                            <div id="resultContent" class="space-y-2 max-h-96 overflow-y-auto">
                                <!-- Resultados serão inseridos aqui -->
                            </div>
                        </div>
                    </div>
                </div>
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
                div.className = 'flex justify-between items-center bg-white border border-gray-200 rounded px-3 py-2 hover:bg-gray-50 transition duration-200';
                div.innerHTML = `
                    <span class="text-sm text-gray-700 font-mono select-all">${item}</span>
                    <button onclick="copyToClipboard('${item}', this)" class="text-blue-500 hover:text-blue-700 text-sm px-2 py-1 rounded hover:bg-blue-50 transition duration-200">
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
                alert('Todos os resultados copiados para a área de transferência!');
            });
        }

        function clearResults() {
            document.getElementById('results').classList.add('hidden');
            document.getElementById('resultContent').innerHTML = '';
        }
    </script>
</x-app-layout>