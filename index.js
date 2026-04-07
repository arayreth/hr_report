// ── Copy code (suivi.php) ──
function copyCode() {
    const code = document.getElementById('code-suivi').textContent.trim();
    const btn = document.getElementById('btn-copy');

    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(code).then(() => {
            btn.textContent = '✅ Copié !';
            setTimeout(() => { btn.textContent = '📋 Copier'; }, 2000);
        });
    } else {
        const textarea = document.createElement('textarea');
        textarea.value = code;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        btn.textContent = '✅ Copié !';
        setTimeout(() => { btn.textContent = '📋 Copier'; }, 2000);
    }
}

// ── Toggle name fields (harassment_report_form.php) ──
function toggleNameFields(isAnonymous) {
    const last = document.getElementById('last_name');
    const first = document.getElementById('first_name');
    if (last) last.disabled = isAnonymous;
    if (first) first.disabled = isAnonymous;
}

// ── File preview + validation taille (harassment_report_form.php) ──
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('media_proof');
    if (!input) return;

    input.addEventListener('change', function () {
        const file = this.files[0];
        const label = document.getElementById('file-label');
        const preview = document.getElementById('file-preview');

        preview.innerHTML = '';

        if (!file) {
            label.innerHTML = '📎 Glissez un fichier ou cliquez pour parcourir';
            return;
        }

        const maxFileSize = 2 * 1024 * 1024 * 1024; // 2 Go

        if (file.size > maxFileSize) {
            label.innerHTML = '❌ Fichier trop volumineux (max 2 Go)';
            label.style.color = '#ff6b6b';
            input.value = '';
            return;
        }

        label.style.color = '';
        const sizeMB = (file.size / 1024 / 1024).toFixed(2);
        label.innerHTML = '✅ ' + file.name + ' — ' + sizeMB + ' Mo';

        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function (e) {
                preview.innerHTML = '<img src="' + e.target.result + '" alt="Aperçu" style="margin-top:12px; max-width:100%; max-height:220px; border-radius:10px; border:1px solid rgba(255,255,255,0.15);">';
            };
            reader.readAsDataURL(file);
        } else if (file.type === 'application/pdf') {
            preview.innerHTML = '<div style="margin-top:12px; padding:14px 18px; background:rgba(255,255,255,0.07); border:1px solid rgba(255,255,255,0.15); border-radius:10px; color:rgba(255,255,255,0.75); font-size:0.88rem;">📄 ' + file.name + '</div>';
        } else if (file.type.startsWith('video/')) {
            const url = URL.createObjectURL(file);
            preview.innerHTML = '<video controls style="margin-top:12px; max-width:100%; border-radius:10px;"><source src="' + url + '" type="' + file.type + '"></video>';
        }
    });

    // Bloquer la soumission si fichier trop grand
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function (e) {
            const file = input.files[0];
            if (file && file.size > 2 * 1024 * 1024 * 1024) {
                e.preventDefault();
                alert('Le fichier dépasse la limite de 2 Go.');
            }
        });
    }
});

 function togglePassword() {
            const input = document.getElementById("password");
            const btn = document.getElementById("toggleBtn");
            const isHidden = input.type === "password";
            input.type = isHidden ? "text" : "password";
            btn.textContent = isHidden ? "🙈" : "👁️";
        }
