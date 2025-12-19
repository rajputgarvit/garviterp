<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Mail.php';

class SupportManager {
    private $db;
    private $mail;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->mail = new Mail();
    }

    /**
     * Create a new ticket
     */
    public function createTicket($userId, $categoryId, $subject, $message, $priority = 'Medium') {
        // Generate Ticket Number
        $ticketNumber = 'TKT-' . date('Y') . '-' . strtoupper(bin2hex(random_bytes(3)));
        
        // Insert Ticket
        $ticketId = $this->db->insert('support_tickets', [
            'ticket_number' => $ticketNumber,
            'user_id' => $userId,
            'category_id' => $categoryId,
            'subject' => $subject,
            'priority' => $priority,
            'status' => 'Open',
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // Insert Initial Message as a Reply
        $this->addReply($ticketId, $userId, $message, null, false); // false = don't send individual reply email for the first message, handled by "New Ticket" email

        // Send Email Notification to Admin
        $user = $this->db->fetchOne("SELECT username, email FROM users WHERE id = ?", [$userId]);
        $this->sendNewTicketEmailToAdmin($ticketNumber, $subject, $user['username'], $message);
        
        // Send Confirmation to User
        $this->sendNewTicketEmailToUser($user['email'], $user['username'], $ticketNumber, $subject);

        return $ticketId;
    }

    /**
     * Add a reply to a ticket
     */
    public function addReply($ticketId, $userId, $message, $attachmentPath = null, $sendNotification = true) {
        $replyId = $this->db->insert('support_ticket_replies', [
            'ticket_id' => $ticketId,
            'user_id' => $userId,
            'message' => $message,
            'attachment_path' => $attachmentPath,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // Update Ticket Timestamp
        $this->db->query("UPDATE support_tickets SET updated_at = NOW() WHERE id = ?", [$ticketId]);

        // Determine if replier is user or admin/agent to send correct notification
        if ($sendNotification) {
            $ticket = $this->getTicketDetails($ticketId);
            
            if ($ticket['user_id'] == $userId) {
                // User replied -> Notify Admin
                $this->db->query("UPDATE support_tickets SET status = 'Open' WHERE id = ?", [$ticketId]); // Re-open if closed/answered
                $this->sendReplyEmailToAdmin($ticket['ticket_number'], $ticket['subject'], $message);
            } else {
                // Admin/Agent replied -> Notify User
                $this->db->query("UPDATE support_tickets SET status = 'Awaiting Reply' WHERE id = ?", [$ticketId]);
                $user = $this->db->fetchOne("SELECT email, username FROM users WHERE id = ?", [$ticket['user_id']]);
                $this->sendReplyEmailToUser($user['email'], $user['username'], $ticket['ticket_number'], $message);
            }
        }

        return $replyId;
    }

    /**
     * Get user's tickets or all tickets (for admin)
     */
    public function getTickets($userId = null, $status = null) {
        $sql = "SELECT t.*, c.name as category_name, u.username as creator_name 
                FROM support_tickets t 
                JOIN support_categories c ON t.category_id = c.id 
                JOIN users u ON t.user_id = u.id";
        
        $params = [];
        $conditions = [];

        if ($userId) {
            $conditions[] = "t.user_id = ?";
            $params[] = $userId;
        }

        if ($status) {
            $conditions[] = "t.status = ?";
            $params[] = $status;
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sql .= " ORDER BY t.updated_at DESC";

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Get single ticket details with replies
     */
    /**
     * Get single ticket details with replies
     */
    public function getTicketDetails($ticketIdOrNumber) {
        $condition = is_numeric($ticketIdOrNumber) ? "t.id = ?" : "t.ticket_number = ?";
        
        $ticket = $this->db->fetchOne(
            "SELECT t.*, c.name as category_name, u.username as creator_name 
             FROM support_tickets t 
             JOIN support_categories c ON t.category_id = c.id 
             JOIN users u ON t.user_id = u.id 
             WHERE $condition", 
            [$ticketIdOrNumber]
        );

        if (!$ticket) return null;

        $ticket['replies'] = $this->db->fetchAll(
            "SELECT r.*, u.username as replier_name, 
            (SELECT GROUP_CONCAT(rol.name) FROM user_roles ur JOIN roles rol ON ur.role_id = rol.id WHERE ur.user_id = u.id) as replier_roles
             FROM support_ticket_replies r 
             JOIN users u ON r.user_id = u.id 
             WHERE r.ticket_id = ? 
             ORDER BY r.created_at ASC", 
            [$ticket['id']]
        );

        return $ticket;
    }

    public function getCategories() {
        return $this->db->fetchAll("SELECT * FROM support_categories ORDER BY name ASC");
    }

    public function updateStatus($ticketId, $status) {
        return $this->db->update('support_tickets', ['status' => $status], 'id = ?', [$ticketId]);
    }

    // --- Email Helpers ---

    private function sendNewTicketEmailToAdmin($ticketNumber, $subject, $username, $messageContent) {
        // In a real app, you'd fetch admin emails. For now, sending to a configured support email.
        $adminEmail = SMTP_FROM_EMAIL; // Or a specific admin email
        $subjectLine = "[New Ticket] $ticketNumber - $subject";
        $body = "
            <h3>New Support Ticket Created</h3>
            <p><strong>Ticket:</strong> $ticketNumber</p>
            <p><strong>User:</strong> $username</p>
            <p><strong>Subject:</strong> $subject</p>
            <hr>
            <p>" . nl2br(htmlspecialchars($messageContent)) . "</p>
            <p><a href='" . BASE_URL . "modules/support/view.php?id=$ticketNumber'>View Ticket</a></p>
        ";
        $this->mail->sendWithResend($adminEmail, $subjectLine, $body);
    }

    private function sendNewTicketEmailToUser($email, $username, $ticketNumber, $subject) {
        $subjectLine = "Ticket Received: $ticketNumber";
        $body = "
            <p>Hello $username,</p>
            <p>We have received your support request regarding \"$subject\".</p>
            <p>Your Ticket ID is: <strong>$ticketNumber</strong></p>
            <p>Our team will review it and get back to you shortly.</p>
            <p><a href='" . BASE_URL . "modules/support/view.php?id=$ticketNumber'>View Ticket Status</a></p>
        ";
        $this->mail->sendWithResend($email, $subjectLine, $body);
    }

    private function sendReplyEmailToUser($email, $username, $ticketNumber, $replyMessage) {
        $subjectLine = "Reply to Ticket: $ticketNumber";
        $body = "
            <p>Hello $username,</p>
            <p>A reply has been added to your ticket <strong>$ticketNumber</strong>:</p>
            <div style='background: #f4f4f4; padding: 15px; border-radius: 5px;'>
                " . nl2br(htmlspecialchars($replyMessage)) . "
            </div>
            <p>You can reply directly to this email or <a href='" . BASE_URL . "modules/support/view.php?id=$ticketNumber'>click here to view the conversation</a>.</p>
        ";
        $this->mail->sendWithResend($email, $subjectLine, $body);
    }

    private function sendReplyEmailToAdmin($ticketNumber, $subject, $replyMessage) {
        $adminEmail = SMTP_FROM_EMAIL;
        $subjectLine = "[Reply] $ticketNumber - $subject";
        $body = "
            <p>New reply on ticket <strong>$ticketNumber</strong>:</p>
            <div style='background: #f4f4f4; padding: 15px; border-radius: 5px;'>
                " . nl2br(htmlspecialchars($replyMessage)) . "
            </div>
            <p><a href='" . BASE_URL . "modules/support/view.php?id=$ticketNumber'>View Ticket</a></p>
        ";
        $this->mail->sendWithResend($adminEmail, $subjectLine, $body);
    }
}
