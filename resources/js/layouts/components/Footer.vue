<template>
  <div class="h-100 d-flex align-center justify-md-space-between justify-center">
    <!-- 👉 Footer: left content -->
    <span class="d-flex align-center text-medium-emphasis">
      &copy;
      {{ new Date().getFullYear() }}
      <VIcon icon="tabler-heart-filled" color="error" size="1.25rem" class="mx-1" />
      Tool Dapodik
    </span>
    <!-- 👉 Footer: right content -->
    <span class="d-md-flex gap-x-4 text-primary d-none"> Dibuat Khusus untuk OKARA </span>
  </div>
</template>
