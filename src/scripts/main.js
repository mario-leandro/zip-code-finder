document.getElementById("cep-form").addEventListener("submit", async function (e) {
    e.preventDefault();

    const cepInput = document.getElementById("cep-input");
    const resultadoDiv = document.getElementById("resultado");
    resultadoDiv.style.display = "none";
    resultadoDiv.innerHTML = "";

    if(cepInput.value.trim() === "") {
        resultadoDiv.style.display = "block";
        resultadoDiv.innerHTML = "<p>Por favor, insira um CEP válido.</p>";
        return;
    }

    try {
        const response = await fetch(`./api/consulta-cep.php`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({ cep: cepInput.value })
        });
        const data = await response.json();

        if (data.erro) {
            resultadoDiv.style.display = "block";
            resultadoDiv.innerHTML = "<p>CEP não encontrado.</p>";
        } else {
            resultadoDiv.style.display = "block";
            resultadoDiv.innerHTML = `
                <ul class="cep-info">
                    <li><strong>CEP:</strong> ${data.cep}</li>
                    <li><strong>Logradouro:</strong> ${data.logradouro}</li>
                    <li><strong>Bairro:</strong> ${data.bairro}</li>
                    <li><strong>Cidade:</strong> ${data.localidade}</li>
                    <li><strong>Estado:</strong> ${data.estado}</li>
                    <li><strong>DDD:</strong> ${data.ddd}</li>
                </ul>

                <button class="salvar-cep" id="salvar-cep">Salvar CEP</button>
            `;
        }
    } catch (error) {
        console.error("Erro ao buscar o CEP:", error);
        resultadoDiv.style.display = "block";
        resultadoDiv.innerHTML = "<p>Ocorreu um erro ao buscar o CEP.</p>";
    }
});
