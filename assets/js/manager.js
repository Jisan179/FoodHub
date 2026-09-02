
function openEditModal(item) {
    document.getElementById('edit_item_id').value = item.item_id;
    document.getElementById('edit_name').value = item.name;
    document.getElementById('edit_description').value = item.description;
    document.getElementById('edit_price').value = item.price;
    document.getElementById('edit_category').value = item.category;
    document.getElementById('edit_status').value = item.status;

    document.getElementById('edit-modal').style.display = 'flex';
}

window.onclick = function (event) {
    let addModal = document.getElementById('add-modal');
    let editModal = document.getElementById('edit-modal');

    if (event.target == addModal) {
        addModal.style.display = "none";
    }
    if (event.target == editModal) {
        editModal.style.display = "none";
    }
}
