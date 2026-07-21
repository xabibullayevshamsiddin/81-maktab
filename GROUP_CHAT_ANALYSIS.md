# Group Chat Tizimi - To'liq Tahlil

## Ma'lumotlar Bazasi Tuzilishi

### 1. chat_groups jadvali
```php
- id (primary key)
- owner_id (foreign key -> users, cascade on delete)
- name (string, 120)
- description (text, nullable)
- privacy (string, 16, default: 'closed') // 'open' yoki 'closed'
- image (string, 255, nullable)
- timestamps

Unique: owner_id (har bir foydalanuvchi faqat 1 ta gruppa ocha oladi)
Index: name
```

### 2. chat_group_members jadvali
```php
- id (primary key)
- chat_group_id (foreign key -> chat_groups, cascade on delete)
- user_id (foreign key -> users, cascade on delete)
- role (string, 32, default: 'member') // 'member' yoki 'admin'
- timestamps

Unique: (chat_group_id, user_id) - bir foydalanuvchi bir gruppada bir marta bo'lishi mumkin
Index: (user_id, chat_group_id)
```

### 3. chat_group_join_requests jadvali
```php
- id (primary key)
- chat_group_id (foreign key -> chat_groups, cascade on delete)
- user_id (foreign key -> users, cascade on delete)
- status (string, 24, default: 'pending') // 'pending', 'accepted', 'rejected'
- timestamps

Unique: (chat_group_id, user_id)
Index: (status, chat_group_id)
```

### 4. chat_messages jadvali (qo'shimcha)
```php
- chat_group_id (foreign key -> chat_groups, nullable, null on delete)
- user_id (foreign key -> users)
- body (text)
- timestamps

Index: chat_group_id
```

## Modelar

### ChatGroup Model
```php
// Konstantlar
PRIVACY_OPEN = 'open'
PRIVACY_CLOSED = 'closed'

// Fillable
owner_id, name, description, privacy, image

// Munosabatlar
- owner(): BelongsTo User
- members(): HasMany ChatGroupMember
- joinRequests(): HasMany ChatGroupJoinRequest
- messages(): HasMany ChatMessage

// Methodlar
- isOwnedBy(User $user): bool
```

### ChatGroupMember Model
```php
// Konstantlar
ROLE_MEMBER = 'member'
ROLE_ADMIN = 'admin'

// Fillable
chat_group_id, user_id, role

// Munosabatlar
- group(): BelongsTo ChatGroup
- user(): BelongsTo User
```

### ChatGroupJoinRequest Model
```php
// Konstantlar
STATUS_PENDING = 'pending'
STATUS_ACCEPTED = 'accepted'
STATUS_REJECTED = 'rejected'

// Fillable
chat_group_id, user_id, status

// Munosabatlar
- group(): BelongsTo ChatGroup
- user(): BelongsTo User

// Methodlar
- isPending(): bool
- isAccepted(): bool
- isRejected(): bool
```

## Controllerlar

### ChatGroupController
```php
// Konstantlar
MAX_GROUPS_JOINED = 3 // Har bir foydalanuvchi ko'pi bilan 3 ta gruppaga a'zo bo'lishi mumkin

// Routelar
GET /chat/groups - index() - Barcha guruhlarni ro'yxati
POST /chat/groups - store() - Yangi gruppa yaratish
PUT /chat/groups/{group} - update() - Gruppani tahrirlash
POST /chat/groups/{group}/image - updateImage() - Gruppa rasmini yuklash
DELETE /chat/groups/{group}/image - deleteImage() - Rasmni o'chirish
DELETE /chat/groups/{group} - destroy() - Gruppani o'chirish
POST /chat/groups/{group}/join - join() - Gruppaga qo'shilish
POST /chat/groups/{group}/leave - leave() - Gruppadan chiqish
GET /chat/groups/{group}/members - members() - A'zolar ro'yxati
PUT /chat/groups/{group}/members/{member} - updateMemberRole() - A'zo rolini o'zgartirish
DELETE /chat/groups/{group}/members/{member} - removeMember() - A'zoni o'chirish
GET /chat/groups/{group}/requests - requests() - Qo'shilish so'rovlari
POST /chat/groups/{group}/requests/{joinRequest}/accept - accept() - So'rovni qabul qilish
POST /chat/groups/{group}/requests/{joinRequest}/reject - reject() - So'rovni rad etish
```

#### Asosiy Methodlar Tahlili

**index()** - Barcha guruhlarni ro'yxati
- Har bir gruppa uchun: id, name, description, image, privacy
- Foydalanuvchi holati: is_owner, is_member, member_role, request_status
- Ruxsatlar: can_manage, can_edit
- Kutilayotgan so'rovlar soni: pending_requests_count
- A'zolar soni: member_count

**store()** - Yangi gruppa yaratish
- Validatsiya: name (required, 2-120), description (nullable, max 500), privacy (open/closed)
- Cheklov: Har bir foydalanuvchi faqat 1 ta gruppa ocha oladi
- Yaratuvchi avtomatik admin rolini oladi

**join()** - Gruppaga qo'shilish
- Agar privacy = 'open': Avtomatik a'zo bo'ladi
- Agar privacy = 'closed': So'rov yuboriladi (status = 'pending')
- Cheklov: Foydalanuvchi ko'pi bilan 3 ta gruppaga a'zo bo'lishi mumkin

**userCanManageGroup()** - Gruppani boshqarish ruxsati
- Gruppa egasi (owner)
- Admin yoki Moderator
- Gruppada admin roli

**userCanEditGroup()** - Gruppani tahrirlash ruxsati
- Gruppa egasi (owner)
- Super Admin

### ChatController (Global Chat + Group Chat integratsiyasi)

**messages()** - Xabarlarni olish
- Agar group_id > 0: Guruh xabarlari
- Agar group_id = 0: Global chat xabarlari
- Guruh xabarlari uchun ruxsat tekshiruv: currentUserCanViewGroup()
- Global chat uchun: global_chat_enabled sozlamasi

**send()** - Xabar yuborish
- Guruh uchun: currentUserCanSendToGroup() tekshiruvi
- Global chat uchun: global_chat_enabled sozlamasi
- Idempotency: 2 soniya ichida bir xil xabarni qayta yuborishni oldini oladi

**currentUserCanViewGroup()** - Guruhni ko'rish ruxsati
- Gruppa egasi
- Admin yoki Moderator
- A'zo (chat_group_members jadvalida)

**currentUserCanSendToGroup()** - Guruhga xabar yuborish ruxsati
- Gruppa egasi
- Admin
- A'zo

## Frontend (JavaScript)

### Asosiy O'zgaruvchilar
```javascript
groupsUrl - Guruhlarni olish URL
groupJoinBase - Guruhga qo'shilish/chiqish base URL
groupRequestsBase - So'rovlarni boshqarish base URL
activeChannel - 'global' yoki 'group'
selectedGroup - Tanlangan gruppa obyekti
groupsData - Barcha guruhlar ma'lumotlari
groupsLoaded - Guruhlar yuklanganmi
```

### Asosiy Methodlar

**renderGroupList(groups)** - Guruhlar ro'yxatini chizish
- Har bir gruppa uchun: avatar, name, member_count, privacy badge, status
- Statuslar: 'Ega', 'A'zo', 'Kutilmoqda', 'Qo'shilish'
- Active class tanlangan gruppaga qo'yiladi

**updateGroupControls()** - Guruh boshqaruv tugmalarini yangilash
- Gruppa tanlanmagan bo'lsa: "Guruh tanlang" ko'rsatiladi
- Tanlangan gruppaga qarab:
  - Join/Leave tugmalari
  - Privacy badge (ochiq/yopiq)
  - Settings tugmasi (faqat egalar uchun)
  - Requests tugmasi (adminlar uchun)

**selectGroup(group)** - Gruppani tanlash
- selectedGroup ni yangilash
- lastId = 0 (xabarlarni qayta yuklash)
- Guruh ro'yxatini yangilash
- Boshqaruv tugmalarini yangilash
- Xabarlarni yuklash

**setActiveChannel(channel)** - Kanalni o'zgartirish
- 'global' yoki 'group'
- Tablarni yangilash
- Guruh shell ni ko'rsatish/yashirish
- Xabarlarni tozalash

**loadGroups()** - Guruhlarni yuklash
- API orqali guruhlarni olish
- groupsData ni yangilash
- Ro'yxatni chizish
- Boshqaruv tugmalarini yangilash

**requestJoinSelectedGroup()** - Gruppaga qo'shilish so'rovi
- POST request yuborish
- Agar joined: Guruhlar ro'yxatini yangilash
- Agar pending: statusni yangilash
- Xatolik bo'lsa toast ko'rsatish

**leaveGroupRequest()** - Gruppadan chiqish
- Confirm dialog
- POST request yuborish
- selectedGroup = null
- Xabarlarni tozalash
- Guruhlarni qayta yuklash

**openCreateGroupDialog()** - Yangi gruppa yaratish dialogi
- Modal dialog ochish
- Name, description, privacy inputlari
- Privacy toggle (open/closed)
- Form validation
- POST request yuborish

## Ruxsatlar Tizimi

### Gruppa Yaratish
- Har bir foydalanuvchi faqat 1 ta gruppa ocha oladi
- Yaratuvchi avtomatik admin rolini oladi

### Gruppaga Qo'shilish
- **Open gruppalar**: Avtomatik a'zo bo'ladi
- **Closed gruppalar**: So'rov yuboriladi, admin tasdiqlashi kerak
- **Limit**: Har bir foydalanuvchi ko'pi bilan 3 ta gruppaga a'zo bo'lishi mumkin

### Gruppani Boshqarish
- **Ega (Owner)**: Hamma narsani qila oladi (edit, delete, manage members)
- **Admin**: Guruhni tahrirlashi, a'zolarni boshqarishi mumkin
- **Moderator**: So'rovlarni ko'radi va qabul/rad qiladi
- **Admin (role)**: Hamma guruhlarni ko'radi va boshqaradi

### Xabar Yuborish
- Guruh a'zolari xabar yuborishi mumkin
- Adminlar har qanday guruhga xabar yuborishi mumkin
- Global chat uchun alohida sozlama (global_chat_enabled)

## UI Elementlari

### HTML Strukturi
```html
<div class="chat-group-shell" id="chat-group-shell">
  <!-- Gruppa yaratish tugmasi -->
  <div class="chat-group-create-bar">
    <button id="chat-group-create-btn">+</button>
  </div>

  <!-- Gruppa ma'lumotlari -->
  <div class="chat-group-meta" id="chat-group-meta">
    <div class="chat-group-meta-labels">
      <strong id="chat-current-group-name"></strong>
      <span id="chat-group-privacy-badge"></span>
    </div>
    <p id="chat-current-group-description"></p>
  </div>

  <!-- Boshqaruv tugmalari -->
  <div class="chat-group-controls" id="chat-group-controls">
    <button id="chat-group-join-btn">Qo'shilish</button>
    <button id="chat-group-leave-btn">Chiqish</button>
    <button id="chat-group-requests-btn">So'rovlalar (0)</button>
    <button id="chat-group-settings-btn">⚙️</button>
  </div>

  <!-- Guruhlar ro'yxati -->
  <div class="chat-group-list" id="chat-group-list"></div>

  <!-- Subpanel (settings/members/requests) -->
  <div class="chat-group-subpanel" id="chat-group-subpanel">
    <div class="chat-group-subpanel-header">
      <button id="chat-group-subpanel-back">←</button>
      <strong id="chat-group-subpanel-title"></strong>
    </div>
    <div id="chat-group-subpanel-body"></div>
  </div>
</div>
```

## Xususiyatlar

1. **Privacy Tizimi**: Open va Closed gruppalar
2. **So'rov Tizimi**: Closed gruppalar uchun qo'shilish so'rovlari
3. **Rol Tizimi**: Owner, Admin, Member
4. **Limitlar**: 1 gruppa yaratish, 3 gruppaga a'zo bo'lish
5. **Rasm Qo'llab-quvvatlash**: Gruppalar uchun rasm yuklash
6. **Real-time**: Polling orqali xabarlarni yangilash
7. **Integratsiya**: Global chat bilan birgalikda ishlash

## Xavfsizlik

1. **Ruxsat Tekshiruvi**: Har bir action uchun role-based access control
2. **CSRF Protection**: Barcha POST requestlarda CSRF token
3. **SQL Injection**: Laravel Eloquent ORM orqali himoya
4. **XSS Protection**: Blade template escaping va JavaScript escaping
5. **Rate Limiting**: Chat uchun throttle middleware
6. **Idempotency**: Xabar yuborishda 2 soniya ichida dublikatni oldini olish

## Migratsiyalar Tarixi

1. `2026_06_23_000000_create_chat_groups_table` - Asosiy jadval
2. `2026_06_23_000010_create_chat_group_members_table` - A'zolar jadvali
3. `2026_06_23_000020_create_chat_group_join_requests_table` - So'rovlar jadvali
4. `2026_06_23_000030_add_chat_group_id_to_chat_messages` - Xabarlarga guruh ID qo'shish
5. `2026_06_23_224700_add_privacy_image_to_chat_groups` - Privacy va image qo'shish, role qo'shish

## Umumiy Arkitektura

Group chat tizimi quyidagi qismlardan iborat:
- **Backend**: Laravel Controllerlar va Modelar
- **Frontend**: Vanilla JavaScript (public-layout.js)
- **Database**: 4 ta asosiy jadval
- **UI**: Blade template komponentlari
- **Integration**: Global chat bilan birgalikda ishladi

Tizim moduldir va global chatdan mustaqil ishlashi mumkin, lekin ular bir UI panelida birlashtirilgan.
