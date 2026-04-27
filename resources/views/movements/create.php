<?php include dirname(__DIR__) . '/includes/header.php'; ?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Novo Registro - Movimentação</title>
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; padding: 20px; }
        .container { max-width: 500px; margin: auto; background: white; padding: 25px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        label { display: block; margin-top: 15px; font-weight: bold; color: #444; }
        input, textarea, select { width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .autocomplete-container { position: relative; width: 100%; }
        .input-group { display: flex; gap: 8px; align-items: flex-start; }
        .autocomplete-list { position: absolute; border: 1px solid #ddd; z-index: 99; top: 100%; left: 0; right: 0; background: white; max-height: 200px; overflow-y: auto; border-radius: 0 0 4px 4px; }
        .autocomplete-item { padding: 10px; cursor: pointer; border-bottom: 1px solid #eee; }
        .autocomplete-item:hover { background-color: #e9e9e9; }
        .btn-add { width: 45px; height: 38px; background: #27ae60; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 20px; font-weight: bold; margin-top: 5px; }
        .signature-wrapper { border: 2px solid #3498db; background: #fff; margin: 15px auto; width: 100%; height: 250px; border-radius: 8px; }
        canvas { width: 100% !important; height: 100% !important; cursor: crosshair; touch-action: none; }
        .btn-salvar { margin-top: 25px; width: 100%; padding: 15px; background: #2980b9; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; font-weight: bold; }
        .modal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:999; }
        .modal-content { background:white; width:300px; margin:100px auto; padding:25px; border-radius:8px; text-align: center; }
    </style>
</head>
<body>

<div class="container">
    <h2 style="text-align: center;">Novo Lançamento</h2>
    <form action="salvar" method="POST" id="formMovimentacao" autocomplete="off">
        <label>Tipo:</label>
        <select name="tipo"><option value="Entrada">Entrada</option><option value="Saída">Saída</option></select>
        <label>Patrimônio:</label>
        <textarea name="patrimonio" required placeholder="Ex: 101, 102"></textarea>
        <label>Data:</label>
        <input type="date" name="entrada" required value="<?php echo date('Y-m-d'); ?>">

        <label>Localidade:</label>
        <div class="input-group">
            <div class="autocomplete-container">
                <input type="text" id="busca_local" placeholder="Buscar local..." autocomplete="off">
                <div id="lista_local" class="autocomplete-list"></div>
                <input type="hidden" name="localidade" id="id_localidade">
            </div>
            <button type="button" class="btn-add" onclick="abrirModal('modalLocal')">+</button>
        </div>

        <label>Usuário:</label>
        <div class="input-group">
            <div class="autocomplete-container">
                <input type="text" id="busca_user" placeholder="Buscar usuário..." autocomplete="off">
                <div id="lista_user" class="autocomplete-list"></div>
                <input type="hidden" name="usuario" id="id_usuario">
            </div>
            <button type="button" class="btn-add" onclick="abrirModal('modalUser')">+</button>
        </div>

        <label style="margin-top:20px;">Assinatura:</label>
        <div class="signature-wrapper"><canvas id="signature-pad"></canvas></div>
        <button type="button" onclick="signaturePad.clear()" style="width:100%; cursor:pointer;">Limpar Assinatura</button>
        <input type="hidden" name="assinatura_data" id="assinatura_data">
        <button type="submit" class="btn-salvar">Gravar Movimentação</button>
    </form>
</div>

<div id="modalLocal" class="modal">
    <div class="modal-content">
        <h4>Novo Local</h4>
        <input type="text" id="novo_nome_local">
        <button type="button" onclick="salvarRapido('localidade', 'novo_nome_local', 'busca_local', 'id_localidade', 'modalLocal')" style="background:#27ae60; color:white; border:none; padding:10px; width:100%; margin-top:10px; border-radius:4px; cursor:pointer;">Cadastrar</button>
        <button type="button" onclick="fecharModal('modalLocal')" style="margin-top:5px; width:100%; cursor:pointer;">Fechar</button>
    </div>
</div>

<div id="modalUser" class="modal">
    <div class="modal-content">
        <h4>Novo Usuário</h4>
        <input type="text" id="novo_nome_user">
        <button type="button" onclick="salvarRapido('usuario', 'novo_nome_user', 'busca_user', 'id_usuario', 'modalUser')" style="background:#27ae60; color:white; border:none; padding:10px; width:100%; margin-top:10px; border-radius:4px; cursor:pointer;">Cadastrar</button>
        <button type="button" onclick="fecharModal('modalUser')" style="margin-top:5px; width:100%; cursor:pointer;">Fechar</button>
    </div>
</div>

<script>
    // CARREGA DADOS DO BANCO
    let localidades = [<?php foreach($localidades ?? [] as $l) { echo "{id:'".$l['id_localidade']."', nome:'".addslashes($l['nome'])."'},"; } ?>];
    let usuarios = [<?php foreach($usuarios ?? [] as $u) { echo "{id:'".$u['id_usuario']."', nome:'".addslashes($u['nome'])."'},"; } ?>];

    function iniciarBusca(inputId, listaId, hiddenId, dadosFonte) {
        const input = document.getElementById(inputId);
        const lista = document.getElementById(listaId);
        const hidden = document.getElementById(hiddenId);

        input.addEventListener('input', function() {
            const valor = this.value.toLowerCase();
            lista.innerHTML = '';
            if (!valor) { hidden.value = ''; return; }
            const filtrados = dadosFonte.filter(item => item.nome.toLowerCase().startsWith(valor));
            filtrados.forEach(item => {
                const div = document.createElement('div');
                div.innerHTML = item.nome; div.classList.add('autocomplete-item');
                div.onclick = () => { input.value = item.nome; hidden.value = item.id; lista.innerHTML = ''; };
                lista.appendChild(div);
            });
        });
        document.addEventListener('click', (e) => { if (e.target !== input) lista.innerHTML = ''; });
    }

    iniciarBusca('busca_local', 'lista_local', 'id_localidade', localidades);
    iniciarBusca('busca_user', 'lista_user', 'id_usuario', usuarios);

    const canvas = document.getElementById('signature-pad');
    const signaturePad = new SignaturePad(canvas, { backgroundColor: 'rgb(255, 255, 255)' });
    function resizeCanvas() {
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        canvas.width = canvas.offsetWidth * ratio; canvas.height = canvas.offsetHeight * ratio;
        canvas.getContext("2d").scale(ratio, ratio); signaturePad.clear();
    }
    window.addEventListener("load", resizeCanvas);

    function abrirModal(id) { document.getElementById(id).style.display = 'block'; }
    function fecharModal(id) { 
        document.getElementById(id).style.display = 'none'; 
        const input = document.getElementById(id).getElementsByTagName('input')[0];
        if(input) input.value = '';
    }

    function salvarRapido(tabela, nomeInputId, txtInputId, hiddenId, modalId) {
        const inputNovoNome = document.getElementById(nomeInputId);
        const nome = inputNovoNome.value.trim();
        if(!nome) return alert("Digite um nome!");

        const baseDados = (tabela === 'localidade') ? localidades : usuarios;
        const existe = baseDados.some(item => item.nome.toLowerCase() === nome.toLowerCase());
        
        if(existe) {
            alert("Erro: Este nome já está cadastrado no sistema!");
            return;
        }

        fetch('cadastrar_rapido', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `tabela=${tabela}&nome=${encodeURIComponent(nome)}`
        })
        .then(res => res.json())
        .then(data => {
            if(data.id) {
                const novoItem = {id: data.id, nome: nome};
                if(tabela === 'localidade') localidades.push(novoItem); else usuarios.push(novoItem);
                document.getElementById(txtInputId).value = nome;
                document.getElementById(hiddenId).value = data.id;
                inputNovoNome.value = '';
                fecharModal(modalId);
            } else {
                alert(data.error || "Erro ao cadastrar.");
            }
        });
    }

    document.getElementById('formMovimentacao').onsubmit = function(e) {
        if (signaturePad.isEmpty() || !document.getElementById('id_localidade').value || !document.getElementById('id_usuario').value) {
            alert("Preencha todos os campos e assine!");
            e.preventDefault();
        } else {
            document.getElementById('assinatura_data').value = signaturePad.toDataURL();
        }
    };
</script>
</body>
</html>
