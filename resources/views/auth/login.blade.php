<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar Sesión - GimnasioV1</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1rem; }
        .login-card { background: white; border-radius: 1.25rem; padding: 2.5rem 2.5rem 2rem; width: 100%; max-width: 420px; box-shadow: 0 25px 60px -12px rgba(0,0,0,0.35), 0 0 0 1px rgba(255,255,255,0.05); }
        .login-card .brand-icon { text-align: center; margin-bottom: 0.75rem; }
        .login-card h1 { font-size: 1.5rem; font-weight: 700; color: #0f172a; text-align: center; }
        .login-card .subtitle { color: #64748b; text-align: center; margin-bottom: 2rem; font-size: 0.9rem; }
        .form-group { margin-bottom: 1.25rem; }
        .form-group label { display: block; font-size: 0.85rem; font-weight: 600; color: #374151; margin-bottom: 0.4rem; }
        .input-wrapper { display: flex; align-items: center; border: 1.5px solid #e2e8f0; border-radius: 0.5rem; transition: all 0.2s; background: white; }
        .input-wrapper:focus-within { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.1); }
        .input-wrapper .form-control { border: none; box-shadow: none; width: 100%; padding: 0.75rem 0.9rem; font-size: 0.9rem; outline: none; background: transparent; }
        .input-wrapper .form-control:focus { box-shadow: none; }
        .input-wrapper .toggle-btn { background: none; border: none; padding: 0 0.75rem; cursor: pointer; color: #94a3b8; display: flex; align-items: center; flex-shrink: 0; line-height: 1; }
        .input-wrapper .toggle-btn:hover { color: #475569; }
        .btn { width: 100%; padding: 0.8rem; border-radius: 0.5rem; font-weight: 600; font-size: 0.95rem; border: none; cursor: pointer; transition: all 0.2s; background: #3b82f6; color: white; }
        .btn:hover { background: #2563eb; }
        .btn:active { transform: scale(0.98); }
        .error { background: #fee2e2; color: #991b1b; padding: 0.75rem 1rem; border-radius: 0.5rem; font-size: 0.85rem; margin-bottom: 1.25rem; text-align: center; border: 1px solid #fecaca; }
        .footer-text { text-align: center; margin-top: 1.75rem; padding-top: 1.25rem; border-top: 1px solid #f1f5f9; font-size: 0.78rem; color: #94a3b8; }
        .toast-container{position:fixed;top:20px;left:50%;transform:translateX(-50%);z-index:99999;display:flex;flex-direction:column;align-items:center;pointer-events:none;}
        .toast{padding:14px 28px;border-radius:12px;font-size:0.9rem;font-weight:500;color:white;box-shadow:0 8px 32px rgba(0,0,0,0.25);margin-bottom:8px;animation:toastIn .3s ease,toastOut .3s ease 1.7s forwards;pointer-events:auto;max-width:520px;text-align:center;line-height:1.4;}
        .toast-error{background:#ef4444;}
        @keyframes toastIn{from{opacity:0;transform:translateY(-24px)}to{opacity:1;transform:translateY(0)}}
        @keyframes toastOut{from{opacity:1;transform:translateY(0)}to{opacity:0;transform:translateY(-24px)}}
    </style>
</head>
<body>
    <div class="login-card">
        <div class="brand-icon">
            <svg fill="none" stroke="#3b82f6" viewBox="0 0 24 24" width="52" height="52" stroke-width="1.5">
                <rect x="3" y="8" width="18" height="8" rx="2"/><rect x="5" y="5" width="2" height="14" rx="1"/><rect x="17" y="5" width="2" height="14" rx="1"/>
            </svg>
        </div>
        <h1>GimnasioV1</h1>
        <p class="subtitle">Inicia sesión para acceder al sistema</p>

        @if($errors->any())
            <div class="error">{{ $errors->first('correo') }}</div>
        @endif

        <form method="POST" action="{{ route('login') }}" novalidate>
            @csrf
            <div class="form-group">
                <label for="correo">Correo electrónico</label>
                <div class="input-wrapper">
                    <input type="text" id="correo" name="correo" class="form-control" value="{{ old('correo') }}" required autofocus placeholder="correo@ejemplo.com">
                </div>
            </div>
            <div class="form-group">
                <label for="contrasena">Contraseña</label>
                <div class="input-wrapper">
                    <input type="password" id="contrasena" name="contrasena" class="form-control" required placeholder="Ingrese su contraseña">
                    <button type="button" class="toggle-btn" onclick="togglePassword()" title="Mostrar contraseña" id="toggleBtn">
                        <svg id="eyeOpen" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                        <svg id="eyeClosed" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none;">
                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                            <line x1="1" y1="1" x2="23" y2="23"/>
                        </svg>
                    </button>
                </div>
            </div>
            <button type="submit" class="btn">Iniciar Sesión</button>
        </form>
        <div class="footer-text">Sistema de Gestión de Gimnasio &copy; {{ date('Y') }}</div>
    </div>

    <script>
        function togglePassword() {
            var input = document.getElementById('contrasena');
            var open = document.getElementById('eyeOpen');
            var closed = document.getElementById('eyeClosed');
            var btn = document.getElementById('toggleBtn');
            if (input.type === 'password') {
                input.type = 'text';
                open.style.display = 'none';
                closed.style.display = '';
                btn.title = 'Ocultar contraseña';
            } else {
                input.type = 'password';
                open.style.display = '';
                closed.style.display = 'none';
                btn.title = 'Mostrar contraseña';
            }
        }
        function mostrarToast(m,t){t=t||'success';var c=document.querySelector('.toast-container');if(!c){c=document.createElement('div');c.className='toast-container';document.body.appendChild(c)}var o=document.createElement('div');o.className='toast toast-'+t;o.textContent=m;c.appendChild(o);setTimeout(function(){if(o.parentNode)o.parentNode.removeChild(o)},2000)}
        document.querySelector('form').addEventListener('submit',function(e){
            var err=document.querySelector('.error');
            if(err)err.style.display='none';
            var c=document.getElementById('correo');
            var p=document.getElementById('contrasena');
            var msgs=[];
            if(!c.value.trim())msgs.push('El correo electrónico es obligatorio.');
            else if(!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(c.value.trim()))msgs.push('Ingrese un correo electrónico válido.');
            if(!p.value)msgs.push('La contraseña es obligatoria.');
            if(msgs.length){
                e.preventDefault();
                mostrarToast(msgs.join(' | '),'error');
            }
        });
    </script>
</body>
</html>
