function getQueryParam(param) {
    const params = new URLSearchParams(window.location.search);
    return params.get(param);
}

const userId = getQueryParam('id');

if (!userId) {
    alert('No se especificó el usuario.');
    document.getElementById('user-name').textContent = 'Error';
    document.getElementById('user-email').textContent = '';
} else {
    fetch(`../../Controller/usuari_per_id.php?id=${userId}`)
        .then(res => {
            if (!res.ok) throw new Error('Usuario no encontrado');
            return res.json();
        })
        .then(data => {
            document.getElementById('user-name').textContent = data.nombre || 'Sin nombre';
            document.getElementById('user-email').textContent = data.email || 'Sin email';
        })
        .catch(err => {
            console.error(err);
            alert(err.message);
            document.getElementById('user-name').textContent = 'Error al cargar usuario';
            document.getElementById('user-email').textContent = '';
        });
}
