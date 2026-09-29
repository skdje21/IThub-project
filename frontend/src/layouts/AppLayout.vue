<template>
  <div style="display: flex; min-height: 100vh;">
    <aside style="width: 240px; background: #1e293b; color: #fff; padding: 20px;">
      <h2 style="margin-bottom: 30px;">IT Hub</h2>
      <nav>
        <ul style="list-style: none; padding: 0;">
          <li style="margin-bottom: 12px;">
            <router-link to="/" style="color: #94a3b8; text-decoration: none;">Dashboard</router-link>
          </li>
          <li style="margin-bottom: 12px;">
            <router-link to="/tickets" style="color: #94a3b8; text-decoration: none;">Заявки</router-link>
          </li>
          <li style="margin-bottom: 12px;">
            <router-link to="/equipment" style="color: #94a3b8; text-decoration: none;">Оборудование</router-link>
          </li>
        </ul>
      </nav>
    </aside>
    <main style="flex: 1; padding: 24px; background: #f1f5f9;">
      <router-view />
    </main>
  </div>
</template>
