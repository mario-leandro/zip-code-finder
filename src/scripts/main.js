// Evento do formulário para buscar o CEP
document.getElementById("cep-form").addEventListener("submit", async function (e) {
    e.preventDefault();

    const cepInput = document.getElementById("cep-input");
    const resultadoDiv = document.getElementById("resultado");
    resultadoDiv.style.display = "none";
    resultadoDiv.innerHTML = "";

    // Verifica se o campo de CEP está vazio
    if (cepInput.value.trim() === "") {
        resultadoDiv.style.display = "block";
        resultadoDiv.innerHTML = "<p>Por favor, insira um CEP válido.</p>";
        return;
    }

    try {
        const response = await fetch(`./api/consulta-cep.php`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ cep: cepInput.value })
        });
        const data = await response.json();

        // Verifica se houve erro na consulta
        // Se não houver erro, exibe os dados e o botão de salvar
        if (!data.erro) {
            resultadoDiv.style.display = "block";
            resultadoDiv.innerHTML = `
                    <ul class="cep-info">
                        <li><strong>CEP:</strong> ${data.cep}</li>
                        <li><strong>Logradouro:</strong> ${data.logradouro}</li>
                        <li><strong>Bairro:</strong> ${data.bairro}</li>
                        <li><strong>Cidade:</strong> ${data.localidade}</li>
                        <li><strong>Estado:</strong> ${data.uf}</li>
                        <li><strong>DDD:</strong> ${data.ddd}</li>
                    </ul>

                    <button class="salvar-cep" id="salvar-cep">Salvar CEP</button>
                `;

            // Evento do botão Salvar CEP
            document.getElementById("salvar-cep").addEventListener("click", async function (e) {
                e.preventDefault();
                try {
                    const salvarResponse = await fetch("./api/salvar-cep.php", {
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify(data)
                    });
                    const salvarData = await salvarResponse.json();

                    if (salvarData.success) {
                        alert("CEP salvo com sucesso!");
                    } else {
                        alert("Erro ao salvar CEP: " + (salvarData.error || salvarData.message));
                    }
                } catch (error) {
                    console.error("Erro ao salvar CEP:", error);
                    alert("Erro de conexão ao salvar CEP.");
                }
            });
        }

    } catch (error) {
        console.error("Erro ao buscar o CEP:", error);
        resultadoDiv.style.display = "block";
        resultadoDiv.innerHTML = "<p>Ocorreu um erro ao buscar o CEP.</p>";
    }
});

// Função para carregar os CEPs salvos ao carregar a página
document.addEventListener("DOMContentLoaded", async (e) => {
    const cepsSalvosDiv = document.getElementById("cep-list");

    try {
        const response = await fetch("./api/mostrar-cep-salvo.php", {
            method: "GET",
            headers: { "Content-Type": "application/json" }
        });
        let result = await response.json();

        if (result.success && result.data.length > 0) {
            cepsSalvosDiv.innerHTML = "";

            result.data.forEach(cep => {
                const cepItem = document.createElement("li");
                cepItem.className = "cep-item";
                cepItem.innerHTML = `
                    <strong>CEP:</strong> ${cep.cep}<br>
                    <strong>Logradouro:</strong> ${cep.logradouro}<br>
                    <strong>Bairro:</strong> ${cep.bairro}<br>
                    <strong>Cidade:</strong> ${cep.cidade}<br>
                    <strong>Estado:</strong> ${cep.estado}<br>
                    <strong>DDD:</strong> ${cep.ddd}<br>
                    <button class="remover-cep" data-cep="${cep.cep}">Remover</button>
                    <hr>
                `;
                cepsSalvosDiv.appendChild(cepItem);

                const btnRemover = cepItem.querySelector(".remover-cep");
                btnRemover.addEventListener("click", async (e) => {
                    e.preventDefault();

                    if (!confirm(`Tem certeza que deseja remover o CEP ${cep.cep}?`)) return;

                    try {
                        const response = await fetch("./api/deletar-cep.php", {
                            method: "DELETE",
                            headers: { "Content-Type": "application/json" },
                            body: JSON.stringify({ cep: cep.cep })
                        });

                        const result = await response.json();

                        if (result.success) {
                            alert(result.message);
                            cepItem.remove();
                        } else {
                            alert("Erro: " + result.message);
                        }
                    } catch (error) {
                        console.error("Erro ao remover CEP:", error);
                        alert("Erro de conexão ao tentar remover o CEP.");
                    }
                });
            });

        } else {
            cepsSalvosDiv.innerHTML = "<p>Nenhum CEP salvo.</p>";
        }
    } catch (error) {
        console.error("Erro ao carregar CEPs salvos:", error);
        cepsSalvosDiv.innerHTML = "<p>Erro ao carregar CEPs salvos.</p>";
    }
});
