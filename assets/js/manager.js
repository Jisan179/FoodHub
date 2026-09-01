// assets/js/manager.js

function openEditModal(item) {
    document.getElementById('edit_item_id').value = item.item_id;
    document.getElementById('edit_name').value = item.name;
    document.getElementById('edit_description').value = item.description;
    document.getElementById('edit_price').value = item.price;
    document.getElementById('edit_category').value = item.category;
    document.getElementById('edit_status').value = item.status;
    
    document.getElementById('edit-modal').style.display = 'flex';
}

window.onclick = function(event) {
    let addModal = document.getElementById('add-modal');
    let editModal = document.getElementById('edit-modal');
    
    if (event.target == addModal) {
        addModal.style.display = "none";
    }
    if (event.target == editModal) {
        editModal.style.display = "none";
    }
}

function updateOrderStatus(orderId, newStatus) {
    if (!confirm(`Are you sure you want to change order #${orderId} status to '${newStatus}'?`)) {
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'update_status');
    formData.append('order_id', orderId);
    formData.append('status', newStatus);
    
    fetch('../controllers/order_controller.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Dynamic UI updates matching the Live Orders 2-Column design
            const orderCard = document.getElementById(`order-card-${orderId}`);
            const kitchenCard = document.getElementById(`kitchen-card-${orderId}`);

            if (newStatus === 'Preparing') {
                // Fade out from New Orders column and reload to populate in Kitchen
                if (orderCard) {
                    orderCard.style.opacity = '0.5';
                }
                window.location.reload();
            } else if (newStatus === 'Ready for Delivery') {
                window.location.reload();
            } else if (newStatus === 'Cancelled') {
                if (orderCard) {
                    orderCard.remove();
                }
            }
        } else {
            alert(data.message || 'Failed to update order status.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('A network error occurred.');
    });
}
