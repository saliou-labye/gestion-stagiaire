<template>
  <div class="auth-bg">
    <div class="auth-shade" />
    <slot />
  </div>
</template>

<style scoped>
.auth-bg { min-height: 100vh; display: grid; place-items: center; padding: 20px; position: relative;
  background: var(--navy) url('/login-bg.jpg') center / cover no-repeat; }
.auth-shade { position: absolute; inset: 0; background: linear-gradient(135deg, rgba(9, 31, 92, .55), rgba(51, 77, 175, .25)); }
.auth-bg > :not(.auth-shade) { position: relative; }
</style>
