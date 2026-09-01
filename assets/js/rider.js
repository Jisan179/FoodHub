document.addEventListener('DOMContentLoaded', function () {
    const dashboard = document.querySelector('[data-rider-dashboard]');
    const feedback = document.querySelector('[data-feedback]');
    if (!dashboard) return;

    const onlineToggle = document.querySelector('[data-online-toggle]');
    const onlineText = document.querySelector('.online-text');
    const onlinePill = document.querySelector('.online-pill');
    let isOnline = window.localStorage.getItem('foodhub-rider-online') !== 'false';
    function renderOnlineState() {
        onlineToggle.textContent = isOnline ? 'Go Offline' : 'Go Online';
        onlineText.textContent = isOnline ? 'Online' : 'Offline';
        onlineText.style.color = isOnline ? '#2563eb' : '#64748b';
        if (onlinePill) {
            onlinePill.innerHTML = '<i></i> ' + (isOnline ? 'Online' : 'Offline');
            onlinePill.querySelector('i').style.background = isOnline ? '#2563eb' : '#94a3b8';
        }
    }
    renderOnlineState();
    onlineToggle.addEventListener('click', function () {
        isOnline = !isOnline;
        window.localStorage.setItem('foodhub-rider-online', String(isOnline));
        renderOnlineState();
    });

    function showFeedback(message, success) {
        feedback.hidden = false;
        feedback.className = 'alert ' + (success ? 'alert-success' : 'alert-error');
        feedback.textContent = message;
    }

    async function postAction(deliveryId, action, note) {
        const response = await fetch('../controllers/rider/dashboard_controller.php', {
            method: 'POST',
            headers: {'X-Requested-With': 'XMLHttpRequest'},
            body: new URLSearchParams({delivery_id: deliveryId, action: action, note: note})
        });
        const result = await response.json();
        if (!response.ok || !result.ok) throw new Error(result.message || 'Request failed.');
        showFeedback(result.message, true);
        window.location.reload();
    }

    dashboard.addEventListener('click', function (event) {
        const actionButton = event.target.closest('[data-action]');
        if (actionButton) {
            const actions = {accept: 'accept', pickup: 'pickup', deliver: 'deliver', update: 'update', cancel: 'cancel'};
            const action = actions[actionButton.dataset.action];
            const wrapper = actionButton.closest('[data-delivery]');
            const note = wrapper.querySelector('.rider-note').value.trim();
            if (!wrapper.dataset.delivery || note.length > 500) {
                showFeedback('Please enter a valid delivery note (maximum 500 characters).', false);
                return;
            }
            if (action === 'cancel' && !window.confirm('Cancel this delivery assignment?')) return;
            actionButton.disabled = true;
            postAction(wrapper.dataset.delivery, action, note).catch(function (error) {
                actionButton.disabled = false;
                showFeedback(error.message, false);
            });
            return;
        }
        const historyButton = event.target.closest('[data-history]');
        if (historyButton) {
            fetch('../controllers/rider/dashboard_controller.php?format=json&history_id=' + encodeURIComponent(historyButton.dataset.history)).then(response => response.json()).then(data => {
                const history = data.history || [];
                showFeedback(history.length ? history.map(item => item.status + ' - ' + item.created_at + (item.note ? ' (' + item.note + ')' : '')).join(' | ') : 'No status history recorded.', Boolean(history.length));
            }).catch(() => showFeedback('Unable to load delivery history.', false));
        }
    });

    document.querySelector('[data-refresh]').addEventListener('click', function () { window.location.reload(); });
});
