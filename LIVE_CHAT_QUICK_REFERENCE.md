# Live Chat System - Quick Reference

## 🚀 Quick Start

### 1. Run Migration
```bash
php artisan migrate
```

### 2. Access Chat
- **Users**: Click green chat button in bottom-right corner (any page)
- **Admins**: Go to `/admin/chats`

---

## 📋 Key Features

### For Users
| Feature | Path | Description |
|---------|------|-------------|
| Start Chat | `/chat` | Begin a new support conversation |
| Chat Window | `/chat/{id}/window` | Active chat interface |
| Send Message | AJAX POST | Real-time message sending |
| Close Chat | Button in chat | End conversation |

### For Admins
| Feature | Path | Description |
|---------|------|-------------|
| Chat Dashboard | `/admin/chats` | View all chats overview |
| Chat Detail | `/admin/chats/{id}` | View full conversation |
| Reply | AJAX POST | Send admin response |
| Update Status | Form | Change chat status/priority |

---

## 🗄️ Database Tables

### chats
- id, user_id, name, email, initial_message
- status: open | waiting | in-progress | closed
- priority: low | medium | high
- assigned_to, started_at, closed_at

### chat_messages
- id, chat_id, user_id, message
- sender_type: user | admin | system
- is_read (boolean)

---

## 🔐 Authorization

- **Users**: Can only view own chats
- **Admins**: Can view all chats (requires is_admin = true)
- **Policy**: `App\Policies\ChatPolicy`

---

## 📁 Key Files

| File | Purpose |
|------|---------|
| `app/Models/Chat.php` | Chat model & relationships |
| `app/Models/ChatMessage.php` | Message model |
| `app/Http/Controllers/ChatController.php` | All chat logic |
| `resources/views/chat/` | User chat views |
| `resources/views/admin/chats/` | Admin chat views |
| `routes/web.php` | User chat routes |
| `routes/admin.php` | Admin chat routes |

---

## 🔄 Real-time Updates

Messages auto-refresh every 2 seconds via JavaScript polling
- User view: AJAX to `/chat/{id}/messages`
- Admin view: AJAX to `/chat/{id}/messages`
- Smooth auto-scroll to latest message

---

## 🎨 Styling

All components use **Tailwind CSS**:
- Green gradient buttons (brand color)
- Blue messages (user), Gray/Green (admin)
- Responsive design (mobile-friendly)
- Status badges with color coding

---

## ✅ Validation

- Message length: 1-1000 characters
- Email format required for anonymous users
- Name max 255 characters
- All inputs sanitized on display

---

## 🧪 Quick Test

1. Login to dashboard
2. Click chat button (bottom-right)
3. Type: "test message"
4. Click Send
5. Go to `/admin/chats` (if admin)
6. Click on chat
7. Type reply
8. Refresh user side → message appears

---

## 🚨 Troubleshooting

| Issue | Solution |
|-------|----------|
| 404 on `/chat` | Run `php artisan migrate` |
| No messages showing | Check browser console for errors |
| Can't access admin chats | Verify `is_admin = true` in users table |
| Messages not updating | Verify AJAX requests in network tab |
| CSRF token error | Check `@csrf` is present in forms |

---

## 📊 Admin Dashboard Stats

Quick view on `/admin/chats`:
- 📘 Open Chats (blue)
- 📙 In Progress (yellow) 
- 📕 Waiting (purple)
- ⬜ Closed (gray)

---

## 🔧 Configuration

### Change Polling Interval
Edit chat views (change 2000 milliseconds):
```javascript
setInterval(loadMessages, 2000); // ms
```

### Change Chat Status Options
Edit dropdown in `/resources/views/admin/chats/show.blade.php`:
```html
<select name="status">
    <option value="open">Open</option>
    <!-- Add more statuses as needed -->
</select>
```

---

## 📱 Mobile Support

✅ Responsive design
✅ Touch-friendly buttons
✅ Full functionality on mobile
✅ Optimized for small screens

---

## 🔒 Security Features

✅ CSRF token protection
✅ Authorization policies
✅ Input validation
✅ XSS prevention (escaped output)
✅ SQL injection protection (Eloquent)
✅ Admin-only routes
✅ User chat isolation

---

## 📈 Scalability Notes

Current implementation:
- ✅ Suitable for 100-500 concurrent users
- ✅ Polling-based (no server push)
- ✅ Database-backed persistence

For 1000+ concurrent users, upgrade to:
- Laravel Echo + Pusher
- Laravel WebSockets
- Socket.io integration

---

## 🎯 Next Features (Optional)

- [ ] File attachments
- [ ] Typing indicators
- [ ] Read receipts
- [ ] Chat rooms/groups
- [ ] Pre-canned responses
- [ ] Chat history export
- [ ] Chat transcripts
- [ ] Analytics & reporting

---

## 📞 Support Routes Reference

```php
// User routes
GET    /chat                      // Show chat page
POST   /chat/start                // Start new chat
GET    /chat/{chat}/window        // Open chat
POST   /chat/{chat}/message       // Send message (AJAX)
GET    /chat/{chat}/messages      // Get messages (AJAX)
POST   /chat/{chat}/close         // Close chat

// Admin routes  
GET    /admin/chats               // List chats
GET    /admin/chats/{chat}        // View chat
POST   /admin/chats/{chat}/reply  // Reply (AJAX)
PUT    /admin/chats/{chat}/status // Update status
```

---

**Status**: ✅ Live and Operational
**Last Updated**: December 26, 2025
