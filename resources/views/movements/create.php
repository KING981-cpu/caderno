<?php
include dirname(__DIR__) . '/includes/header.php';

$tipo = $defaultTipo ?? 'Entrada';
$patrimonio = $defaultPatrimonio ?? '';
$autoDate = $autoDate ?? false;
$today = date('Y-m-d');
?>

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
        <?php if ($autoDate): ?>
            <input type="hidden" name="tipo" value="Saída">
            <p><strong>Tipo:</strong> Saída</p>
        <?php else: ?>
            <label>Tipo:</label>
            <select name="tipo">
                <option value="Entrada" <?php echo $tipo === 'Entrada' ? 'selected' : ''; ?>>Entrada</option>
                <option value="Saída" <?php echo $tipo === 'Saída' ? 'selected' : ''; ?>>Saída</option>
            </select>
        <?php endif; ?>

        <label>Patrimônio:</label>
        <textarea name="patrimonio" required placeholder="Ex: 101, 102"><?php echo htmlspecialchars($patrimonio); ?></textarea>

        <?php if ($autoDate): ?>
            <label>Data:</label>
            <p><?php echo date('d/m/Y', strtotime($today)); ?></p>
            <input type="hidden" name="data" value="<?php echo $today; ?>">
        <?php else: ?>
            <label>Data:</label>
            <input type="date" name="data" required value="<?php echo $today; ?>">
        <?php endif; ?>

        <label>Localidade:</label>
        <div id="localidades-container"></div>
        <button type="button" class="btn-add" onclick="adicionarLocalidade()" style="width: 45px; height: 38px; margin-top: 10px;">+</button>

        <label style="margin-top: 20px;">Usuário:</label>
        <div id="usuarios-container"></div>
        <button type="button" class="btn-add" onclick="adicionarUsuario()" style="width: 45px; height: 38px; margin-top: 10px;">+</button>

        <label style="margin-top:30px;">Assinatura:</label>
        <div class="signature-wrapper"><canvas id="signature-pad"></canvas></div>
        <button type="button" onclick="signaturePad.clear()" style="width:100%; cursor:pointer;">Limpar Assinatura</button>
        <input type="hidden" name="assinatura_data" id="assinatura_data">
        <button type="submit" class="btn-salvar">Gravar Movimentação</button>
    </form>
</div>



<script>
    let localidades = [<?php foreach($localidades ?? [] as $l) { echo "{id:'".$l['id_localidade']."', nome:'".addslashes($l['nome'])."'},"; } ?>];
    let usuarios = [<?php foreach($usuarios ?? [] as $u) { echo "{id:'".$u['id_usuario']."', nome:'".addslashes($u['nome'])."'},"; } ?>];
    
    // Armazena os IDs selecionados
    let localidadesSelecionadas = [];
    let usuariosSelecionados = [];

    // Cria autocomplete para um campo
    function criarAutocomplete(inputId, listaId, dadosFonte, onSelect) {
        const input = document.getElementById(inputId);
        const lista = document.getElementById(listaId);

        input.addEventListener('input', function() {
            const valor = this.value.toLowerCase().trim();
            lista.innerHTML = '';
            if (!valor) return;
            
            const filtrados = dadosFonte.filter(item => 
                item.nome.toLowerCase().includes(valor)
            );
            
            filtrados.forEach(item => {
                const div = document.createElement('div');
                div.className = 'autocomplete-item';
                div.textContent = item.nome;
                div.onclick = () => {
                    onSelect(item);
                    input.value = '';
                    lista.innerHTML = '';
                };
                lista.appendChild(div);
            });
        });

        document.addEventListener('click', (e) => { 
            if (e.target !== input && !lista.contains(e.target)) {
                lista.innerHTML = ''; 
            }
        });
    }

    // Adiciona nova linha de localidade
    function adicionarLocalidade() {
        const container = document.getElementById('localidades-container');
        const id = 'localidade_' + Date.now();
        const idLista = id + '_lista';
        
        const div = document.createElement('div');
        div.className = 'input-group';
        div.innerHTML = `
            <div class="autocomplete-container" style="flex: 1;">
                <input type="text" id="${id}" placeholder="Buscar ou digitar localidade..." autocomplete="off">
                <div id="${idLista}" class="autocomplete-list"></div>
            </div>
            <button type="button" class="btn-add" onclick="confirmarLocalidade('${id}')">✓</button>
        `;
        container.appendChild(div);
        
        criarAutocomplete(id, idLista, localidades, (item) => {
            document.getElementById(id).value = item.nome;
            confirmarLocalidade(id, item.id);
        });
        
        document.getElementById(id).focus();
    }

    function confirmarLocalidade(inputId, itemId = null) {
        const input = document.getElementById(inputId);
        const nome = input.value.trim();
        
        if (!nome) return alert("Digite uma localidade!");

        // Se não foi selecionado da lista, criar novo
        if (!itemId) {
            const existe = localidades.some(l => l.nome.toLowerCase() === nome.toLowerCase());
            if (existe) {
                itemId = localidades.find(l => l.nome.toLowerCase() === nome.toLowerCase()).id;
            } else {
                // Cadastrar novo
                fetch('cadastrar_rapido', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `tabela=localidade&nome=${encodeURIComponent(nome)}`
                })
                .then(res => res.json())
                .then(data => {
                    if (data.id) {
                        itemId = data.id;
                        localidades.push({id: itemId, nome: nome});
                        adicionarLocalidadeConfirmada(itemId, nome, inputId);
                    } else {
                        alert(data.error || "Erro ao cadastrar localidade");
                    }
                });
                return;
            }
        }
        
        adicionarLocalidadeConfirmada(itemId, nome, inputId);
    }

    function adicionarLocalidadeConfirmada(id, nome, inputId) {
        const input = document.getElementById(inputId);
        const container = input.closest('.input-group');
        
        // Criar tag da localidade selecionada
        const tag = document.createElement('div');
        tag.className = 'localidade-tag';
        tag.style.cssText = 'display: inline-block; background: #27ae60; color: white; padding: 5px 10px; border-radius: 4px; margin: 5px 5px 5px 0; position: relative;';
        tag.innerHTML = `${nome} <input type="hidden" name="localidade" value="${id}"> <button type="button" onclick="this.parentElement.remove()" style="background: none; border: none; color: white; cursor: pointer; margin-left: 5px;">✕</button>`;
        
        container.parentElement.insertBefore(tag, container);
        container.remove();
    }

    // Adiciona nova linha de usuário
    function adicionarUsuario() {
        const container = document.getElementById('usuarios-container');
        const id = 'usuario_' + Date.now();
        const idLista = id + '_lista';
        
        const div = document.createElement('div');
        div.className = 'input-group';
        div.innerHTML = `
            <div class="autocomplete-container" style="flex: 1;">
                <input type="text" id="${id}" placeholder="Buscar ou digitar usuário..." autocomplete="off">
                <div id="${idLista}" class="autocomplete-list"></div>
            </div>
            <button type="button" class="btn-add" onclick="confirmarUsuario('${id}')">✓</button>
        `;
        container.appendChild(div);
        
        criarAutocomplete(id, idLista, usuarios, (item) => {
            document.getElementById(id).value = item.nome;
            confirmarUsuario(id, item.id);
        });
        
        document.getElementById(id).focus();
    }

    function confirmarUsuario(inputId, itemId = null) {
        const input = document.getElementById(inputId);
        const nome = input.value.trim();
        
        if (!nome) return alert("Digite um usuário!");

        if (!itemId) {
            const existe = usuarios.some(u => u.nome.toLowerCase() === nome.toLowerCase());
            if (existe) {
                itemId = usuarios.find(u => u.nome.toLowerCase() === nome.toLowerCase()).id;
            } else {
                fetch('cadastrar_rapido', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `tabela=usuario&nome=${encodeURIComponent(nome)}`
                })
                .then(res => res.json())
                .then(data => {
                    if (data.id) {
                        itemId = data.id;
                        usuarios.push({id: itemId, nome: nome});
                        adicionarUsuarioConfirmado(itemId, nome, inputId);
                    } else {
                        alert(data.error || "Erro ao cadastrar usuário");
                    }
                });
                return;
            }
        }
        
        adicionarUsuarioConfirmado(itemId, nome, inputId);
    }

    function adicionarUsuarioConfirmado(id, nome, inputId) {
        const input = document.getElementById(inputId);
        const container = input.closest('.input-group');
        
        const tag = document.createElement('div');
        tag.className = 'usuario-tag';
        tag.style.cssText = 'display: inline-block; background: #3498db; color: white; padding: 5px 10px; border-radius: 4px; margin: 5px 5px 5px 0; position: relative;';
        tag.innerHTML = `${nome} <input type="hidden" name="usuario" value="${id}"> <button type="button" onclick="this.parentElement.remove()" style="background: none; border: none; color: white; cursor: pointer; margin-left: 5px;">✕</button>`;
        
        container.parentElement.insertBefore(tag, container);
        container.remove();
    }

    function removerUsuario(inputId) {
        document.getElementById(inputId).closest('.input-group').remove();
    }

    // Inicializa com uma linha de cada
    
    const canvas = document.getElementById('signature-pad');
    const signaturePad = new SignaturePad(canvas, { backgroundColor: 'rgb(255, 255, 255)' });
    
    function resizeCanvas() {
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        canvas.width = canvas.offsetWidth * ratio; 
        canvas.height = canvas.offsetHeight * ratio;
        canvas.getContext("2d").scale(ratio, ratio); 
        signaturePad.clear();
    }
    
    window.addEventListener("load", resizeCanvas);

    document.getElementById('formMovimentacao').onsubmit = function(e) {
        const localidades = document.querySelectorAll('input[name="localidade"]');
        const usuarios = document.querySelectorAll('input[name="usuario"]');
        
        if (signaturePad.isEmpty() || localidades.length === 0 || usuarios.length === 0) {
            alert("Preencha localidade, usuário e assine!");
            e.preventDefault();
        } else {
            document.getElementById('assinatura_data').value = signaturePad.toDataURL();
        }
    };
</script>
</body>
</html>
