import axios from 'axios';

export function setupProjectTags() {
    const form = document.getElementById('tagsForm');
    const tagList = document.getElementById('existingTags');

    if (!form || !tagList) return;

    const projectId = form.querySelector('input[name="project_id"]')?.value;
    const input = form.querySelector('input[name="tag_name"]');

    if (!projectId) return;

    async function loadTags() {
        try {
            const res = await axios.get('/tags', {
                params: { project_id: projectId }
            });

            tagList.innerHTML = '';

            if (res.data.length === 0) {
                tagList.innerHTML =
                    '<li class="text-slate-400">Nenhuma tag para este projeto.</li>';
                return;
            }

            res.data.forEach(tag => {
                const li = document.createElement('li');
                li.className = 'flex justify-between items-center';

                li.innerHTML = `
                    <span>${tag.name}</span>
                    <button class="text-red-500 text-sm">Eliminar</button>
                `;

                li.querySelector('button').addEventListener('click', async () => {
                    await axios.delete(`/tags/${tag.tag_id}`);
                    loadTags();
                });

                tagList.appendChild(li);
            });
        } catch (err) {
            console.error('Failed to load tags', err);
        }
    }

    form.addEventListener('submit', async e => {
        e.preventDefault();

        const name = input.value.trim();
        if (!name) return;

        try {
            await axios.post('/tags', {
                name,
                project_id: projectId
            });

            input.value = '';
            loadTags();
        } catch (err) {
            console.error('Failed to add tag', err);
        }
    });

    loadTags();
}

export function setupTaskTagSelection() {
    document.querySelectorAll('.tag-toggle').forEach(button => {
        button.addEventListener('click', () => {
            const tagId = button.dataset.tagId;
            const wrapper = button.closest('div');
            const hiddenInput = wrapper.parentElement.querySelector('input[name="tags"]');

            if (!hiddenInput) return;

            let selected = hiddenInput.value
                ? hiddenInput.value.split(',').filter(Boolean)
                : [];

            const isSelected = selected.includes(tagId);

            if (isSelected) {
                selected = selected.filter(id => id !== tagId);
                button.classList.remove('bg-atlas-500', 'text-white', 'border-atlas-600');
                button.classList.add('bg-gray-50', 'text-gray-700', 'border-gray-300');
            } else {
                selected.push(tagId);
                button.classList.add('bg-atlas-500', 'text-white', 'border-atlas-600');
                button.classList.remove('bg-gray-50', 'text-gray-700', 'border-gray-300');
            }

            hiddenInput.value = selected.join(',');
        });
    });
}
