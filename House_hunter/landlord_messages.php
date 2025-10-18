<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'landlord') {
    header("Location: pages/login.php");
    exit();
}

require_once 'config/database.php';
$database = new Database();
$db = $database->getConnection();

$landlord_id = $_SESSION['user_id'];

$query = "SELECT c.*, p.title as property_title, p.location as property_location 
          FROM contacts c 
          JOIN properties p ON c.property_id = p.id 
          WHERE p.landlord_id = ? 
          ORDER BY c.created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute([$landlord_id]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

$unreadQuery = "SELECT COUNT(*) as unread_count 
                FROM contacts c 
                JOIN properties p ON c.property_id = p.id 
                WHERE p.landlord_id = ? AND c.status = 'unread'";
$unreadStmt = $db->prepare($unreadQuery);
$unreadStmt->execute([$landlord_id]);
$unread_count = $unreadStmt->fetch(PDO::FETCH_ASSOC)['unread_count'];
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/navbar.php'; ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Messages from Tenants</h2>
                <?php if($unread_count > 0): ?>
                    <span class="badge bg-danger"><?php echo $unread_count; ?> Unread</span>
                <?php endif; ?>
            </div>

            <?php if(empty($messages)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    You haven't received any messages from tenants yet.
                </div>
            <?php else: ?>
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Status</th>
                                        <th>Property</th>
                                        <th>From</th>
                                        <th>Contact Info</th>
                                        <th>Message Preview</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($messages as $message): ?>
                                    <tr class="<?php echo $message['status'] == 'unread' ? 'table-active' : ''; ?>">
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $message['status'] == 'unread' ? 'danger' : 
                                                     ($message['status'] == 'read' ? 'success' : 'primary'); 
                                            ?>">
                                                <?php echo ucfirst($message['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($message['property_title']); ?></strong><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($message['property_location']); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($message['name']); ?></td>
                                        <td>
                                            <div><i class="fas fa-envelope me-1"></i> <?php echo htmlspecialchars($message['email']); ?></div>
                                            <div><i class="fas fa-phone me-1"></i> <?php echo htmlspecialchars($message['phone']); ?></div>
                                        </td>
                                        <td>
                                            <div class="message-preview">
                                                <?php 
                                                $preview = strip_tags($message['message']);
                                                echo htmlspecialchars(substr($preview, 0, 50)); 
                                                if(strlen($preview) > 50): ?>...<?php endif; ?>
                                            </div>
                                        </td>
                                        <td><?php echo date('M j, Y g:i A', strtotime($message['created_at'])); ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary view-message" 
                                                        data-message-id="<?php echo $message['id']; ?>"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#messageModal">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <?php if($message['status'] == 'unread'): ?>
                                                <button class="btn btn-outline-success mark-read" 
                                                        data-message-id="<?php echo $message['id']; ?>">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <?php endif; ?>
                                                <button class="btn btn-outline-danger delete-message" 
                                                        data-message-id="<?php echo $message['id']; ?>">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="modal fade" id="messageModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Message Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="messageDetails">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="#" class="btn btn-primary" id="replyEmailBtn">
                    <i class="fas fa-reply"></i> Reply via Email
                </a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {

    document.querySelectorAll('.view-message').forEach(btn => {
        btn.addEventListener('click', function() {
            const messageId = this.dataset.messageId;
            loadMessageDetails(messageId);
        });
    });


    document.querySelectorAll('.mark-read').forEach(btn => {
        btn.addEventListener('click', function() {
            const messageId = this.dataset.messageId;
            markMessageAsRead(messageId);
        });
    });

    document.querySelectorAll('.delete-message').forEach(btn => {
        btn.addEventListener('click', function() {
            const messageId = this.dataset.messageId;
            deleteMessage(messageId);
        });
    });
});

function loadMessageDetails(messageId) {
    fetch(`api/get_message.php?id=${messageId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const message = data.message;
                document.getElementById('messageDetails').innerHTML = `
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Property:</strong> ${escapeHtml(message.property_title)}<br>
                            <strong>Location:</strong> ${escapeHtml(message.property_location)}
                        </div>
                        <div class="col-md-6">
                            <strong>From:</strong> ${escapeHtml(message.name)}<br>
                            <strong>Date:</strong> ${new Date(message.created_at).toLocaleString()}
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Email:</strong> ${escapeHtml(message.email)}<br>
                            <strong>Phone:</strong> ${escapeHtml(message.phone)}
                        </div>
                        <div class="col-md-6">
                            <strong>Status:</strong> <span class="badge bg-${message.status === 'unread' ? 'danger' : 'success'}">${message.status}</span>
                        </div>
                    </div>
                    <div class="border-top pt-3">
                        <strong>Message:</strong>
                        <div class="mt-2 p-3 bg-light rounded">${escapeHtml(message.message).replace(/\n/g, '<br>')}</div>
                    </div>
                `;
                
                document.getElementById('replyEmailBtn').href = `mailto:${message.email}?subject=Re: Inquiry about ${encodeURIComponent(message.property_title)}`;
                
                if (message.status === 'unread') {
                    markMessageAsRead(messageId);
                }
            } else {
                alert('Error loading message details');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading message details');
        });
}

function markMessageAsRead(messageId) {
    fetch('api/update_message_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            message_id: messageId,
            status: 'read'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error updating message status');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating message status');
    });
}

function deleteMessage(messageId) {
    if (confirm('Are you sure you want to delete this message?')) {
        fetch('api/delete_message.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                message_id: messageId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error deleting message');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting message');
        });
    }
}

function escapeHtml(unsafe) {
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}
</script>

<style>
.message-preview {
    max-width: 200px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.table-active {
    background-color: #e3f2fd !important;
}
</style>

<?php include 'includes/footer.php'; ?>