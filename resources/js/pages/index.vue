<script setup>
definePage({
  meta: {
    action: "read",
    subject: "Web",
  },
});
const sekolah_id = ref();
const sekolah = ref();
const items = ref([]);
const isAllowed = ref(true);
const clickMe = async () => {
  await $api("/sekolah", {
    method: "POST",
    body: {
      sekolah_id: sekolah_id.value,
    },
    async onResponse({ response }) {
      let getData = response._data;
      isAllowed.value = getData.allowed;
      await fetchData();
    },
  });
};
onMounted(async () => {
  await fetchData();
});
const isDisabled = ref(false);
const fetchData = async () => {
  isDisabled.value = true;
  try {
    const response = await useApi(createUrl("/sekolah"));
    let getData = response.data.value;
    items.value = getData.sekolah;
    sekolah.value = getData.user?.sekolah;
  } catch (error) {
    console.error(error);
  } finally {
    isDisabled.value = false;
  }
};
const isDialogVisible = ref(false);
const resetSekolah = () => {
  isDialogVisible.value = true;
};
const reset = async () => {
  isDialogVisible.value = false;
  await useApi(createUrl("/reset"));
  appLogout();
};
</script>
<template>
  <VCard color="#007BB6">
    <VCardItem>
      <template #prepend>
        <VIcon size="1.9rem" color="white" icon="tabler-database" />
      </template>
      <VCardTitle class="text-white"> Tool Dapodik </VCardTitle>
    </VCardItem>
    <VCardText>
      <p class="clamp-text text-white mb-0">Harap Gunakan dengan BIJAK!</p>
    </VCardText>
  </VCard>
  <VCard class="mt-4">
    <VCardItem>
      <template #append v-if="sekolah">
        <VBtn color="error" @click="resetSekolah">Reset</VBtn>
      </template>
      <VCardTitle class="text-white">
        {{ sekolah ? "Data" : "Registrasi" }} Sekolah
      </VCardTitle>
    </VCardItem>
    <VCardText v-if="sekolah">
      <VAlert color="error" v-if="!isAllowed"> NPSN tidak terdaftar! </VAlert>
      <VTable>
        <tbody>
          <tr>
            <td>Nama</td>
            <td>{{ sekolah.nama }}</td>
          </tr>
          <tr>
            <td>NPSN</td>
            <td>{{ sekolah.npsn }}</td>
          </tr>
        </tbody>
      </VTable>
    </VCardText>
    <VCardText v-else>
      <AppAutocomplete
        v-model="sekolah_id"
        :items="items"
        item-title="nama"
        item-value="sekolah_id"
        placeholder="Pilih Sekolah"
        :disabled="isDisabled"
        :loading="isDisabled"
      >
        <template #append>
          <VBtn :icon="$vuetify.display.smAndDown" @click="clickMe">
            <VIcon icon="tabler-device-floppy" color="#fff" size="22" />
            <span v-if="$vuetify.display.mdAndUp" class="ms-3">Simpan</span>
          </VBtn>
        </template>
      </AppAutocomplete>
    </VCardText>
    <VDialog v-model="isDialogVisible" persistent class="v-dialog-sm">
      <DialogCloseBtn @click="isDialogVisible = !isDialogVisible" />
      <VCard title="Apakah Anda yakin?">
        <VCardText> Tindakan ini akan mengambalikan Aplikasi ke awal! </VCardText>
        <VCardText class="d-flex justify-end gap-3 flex-wrap">
          <VBtn color="secondary" variant="tonal" @click="isDialogVisible = false">
            Batal
          </VBtn>
          <VBtn @click="reset"> Yakin </VBtn>
        </VCardText>
      </VCard>
    </VDialog>
  </VCard>
</template>
