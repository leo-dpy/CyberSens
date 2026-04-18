// Init Quill
var quill = new Quill('#editor', {
    theme: 'snow',
    placeholder: 'Rédigez le contenu de votre cours ici...',
    modules: {
        imageResize: {
            displaySize: true
        },
        toolbar: [
            [{ 'header': [1, 2, 3, false] }],
            [{ 'size': ['small', false, 'large', 'huge'] }],
            ['bold', 'italic', 'underline', 'strike', 'code-block'],
            [{ 'color': [] }, { 'background': [] }],
            [{ 'align': [] }],
            [{ 'list': 'ordered' }, { 'list': 'bullet' }],
            ['link', 'image', 'video', 'clean']
        ]
    }
});

document.getElementById('courseForm').onsubmit = function () {
    document.getElementById('content').value = quill.root.innerHTML;
};

// Aides à la sélection
function selectDifficulty(diff, el) {
    document.getElementById('difficultyInput').value = diff;
    document.querySelectorAll('.difficulty-option').forEach(e => e.classList.remove('selected'));
    el.classList.add('selected');
}

function selectIcon(icon, el) {
    document.getElementById('iconInput').value = icon;
    document.querySelectorAll('.icon-option').forEach(e => e.classList.remove('selected'));
    el.classList.add('selected');
}

function selectTheme(theme, el) {
    document.getElementById('themeInput').value = theme;
    document.querySelectorAll('.theme-option').forEach(e => e.classList.remove('selected'));
    el.classList.add('selected');
}

function updatePreview() {
    // Optionnel : Implémentation de l'aperçu en temps réel
}
