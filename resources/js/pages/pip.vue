<script setup>
import { useDebounceFn } from "@vueuse/core";
definePage({
  meta: {
    action: "read",
    subject: "Web",
  },
});
onMounted(async () => {
  await fetchData();
});
const isLoading = ref(false);
const sekolah = ref();
const fetchData = async () => {
  isLoading.value = true;
  try {
    const response = await useApi(createUrl("/cek-sekolah"));
    let getData = response.data.value;
    sekolah.value = getData.sekolah;
    form.value.semester_id = getData.semester.semester_id;
    if (sekolah.value) {
      fetchItem();
    }
  } catch (error) {
    console.error(error);
  } finally {
    isLoading.value = false;
  }
};
const headers = [
  {
    title: "Nama",
    key: "nama",
    sortable: true,
    nowrap: true,
  },
  {
    title: "NISN",
    key: "nisn",
    align: "center",
  },
  {
    title: "Kelas",
    key: "kelas",
    align: "center",
    sortable: false,
  },
  {
    title: "Penerima PIP",
    key: "penerima_kip",
    align: "center",
    sortable: false,
  },
  {
    title: "Layak PIP",
    key: "layak_pip",
    align: "center",
    sortable: false,
  },
  {
    title: "Alasan Layak PIP",
    key: "id_layak_pip",
    align: "center",
    sortable: false,
  },
  {
    title: "aksi",
    key: "aksi",
    align: "center",
    sortable: false,
  },
];
const options = ref({
  page: 1,
  itemsPerPage: 10,
  searchQuery: "",
  selectedRole: null,
  sortby: "nama",
  sortbydesc: "ASC",
});
const loadingTable = ref(false);
const items = ref([]);
const total = ref(0);
const layak_pip = ref([]);
const updateSortBy = (val) => {
  options.value.sortby = val[0]?.key;
  options.value.sortbydesc = val[0]?.order;
};
const form = ref({
  semester_id: null,
  layak_pip: {},
  id_layak_pip: {},
});
const isSnackbarTopEndVisible = ref(false);
const fetchItem = async () => {
  loadingTable.value = true;
  try {
    const response = await useApi(
      createUrl("/pip", {
        query: {
          q: options.value.searchQuery,
          page: options.value.page,
          per_page: options.value.itemsPerPage,
          sortby: options.value.sortby,
          sortbydesc: options.value.sortbydesc,
        },
      })
    );
    let getData = response.data.value;
    items.value = getData.data.data;
    total.value = getData.data.total;
    layak_pip.value = getData.layak_pip;
    items.value.forEach((e) => {
      form.value.layak_pip[e.peserta_didik_id] = e.layak_pip;
      form.value.id_layak_pip[e.peserta_didik_id] = e.id_layak_pip
        ? parseInt(e.id_layak_pip)
        : null;
    });
  } catch (error) {
    console.error(error);
  } finally {
    loadingTable.value = false;
  }
};
watch(
  options,
  useDebounceFn(() => {
    fetchItem();
  }, 500),
  { deep: true }
);
watch(
  () => options.value.searchQuery,
  () => {
    options.value.page = 1;
  }
);
const loadings = ref([]);
const id = ref();
const errors = ref({
  layak_pip: {},
  id_layak_pip: {},
});
const simpan = async (peserta_didik_id) => {
  id.value = peserta_didik_id;
  loadings.value[peserta_didik_id] = true;
  await $api("/pip", {
    method: "POST",
    body: {
      peserta_didik_id: peserta_didik_id,
      layak_pip: form.value.layak_pip[peserta_didik_id],
      id_layak_pip: form.value.id_layak_pip[peserta_didik_id],
    },
    async onResponse({ response }) {
      console.log(form.value.layak_pip[peserta_didik_id]);

      const getData = response._data;
      loadings.value[peserta_didik_id] = false;
      errors.value.layak_pip[peserta_didik_id] = getData.errors?.layak_pip ?? undefined;
      errors.value.id_layak_pip[peserta_didik_id] =
        getData.errors?.id_layak_pip ?? undefined;
      if (!getData.errors) {
        isSnackbarTopEndVisible.value = true;
      }
    },
  });
};
const changeLayak = (peserta_didik_id, val) => {
  if (!parseInt(val)) {
    form.value.id_layak_pip[peserta_didik_id] = null;
  }
};
</script>
<template>
  <template v-if="isLoading">
    <VCard class="text-center">
      <VProgressCircular :size="60" indeterminate color="error" class="my-10" />
    </VCard>
  </template>
  <template v-else>
    <VCard v-if="sekolah">
      <VCardItem class="pb-4">
        <VCardTitle>Data Periodik Siswa</VCardTitle>
      </VCardItem>
      <VDivider />
      <VCardText class="d-flex flex-wrap gap-4">
        <div class="d-flex gap-2 align-center">
          <AppSelect
            v-model="options.itemsPerPage"
            :items="[
              { value: 10, title: '10' },
              { value: 25, title: '25' },
              { value: 50, title: '50' },
              { value: 100, title: '100' },
            ]"
            style="inline-size: 15.5rem"
          />
        </div>
        <VSpacer />
        <div class="d-flex align-center flex-wrap gap-4">
          <!-- 👉 Search  -->
          <AppTextField
            v-model="options.searchQuery"
            placeholder="Cari data"
            style="inline-size: 15.625rem"
          />
        </div>
      </VCardText>
      <VDivider />
      <VDataTableServer
        v-model:page="options.page"
        :items-per-page="options.itemsPerPage"
        :items-per-page-options="[
          { value: 10, title: '10' },
          { value: 20, title: '20' },
          { value: 50, title: '50' },
        ]"
        :items="items"
        :server-items-length="total"
        :items-length="total"
        :headers="headers"
        items-per-page-text="Item"
        :options="options"
        :loading="loadingTable"
        loading-text="Loading..."
        @update:sortBy="updateSortBy"
        :header-props="{ class: 'text-no-wrap' }"
      >
        <template #item.kelas="{ item }">
          {{ item.kelas?.nama }}
        </template>
        <template #item.penerima_kip="{ item }">
          <VChip :color="item.penerima_kip == 1 ? 'success' : 'error'">
            {{ item.penerima_kip == 1 ? "Ya" : "Tidak" }}
          </VChip>
        </template>
        <template #item.berat="{ item }">
          <AppTextField
            v-model="form.berat_badan[item.peserta_didik_id]"
            :error-messages="errors.berat_badan[item.peserta_didik_id]"
          />
        </template>
        <template #item.layak_pip="{ item }">
          <AppSelect
            :items="[
              {
                title: 'Ya',
                value: '1',
              },
              {
                title: 'Tidak',
                value: '0',
              },
            ]"
            v-model="form.layak_pip[item.peserta_didik_id]"
            :error-messages="errors.layak_pip[item.peserta_didik_id]"
            @update:model-value="changeLayak(item.peserta_didik_id, $event)"
          />
        </template>
        <template #item.id_layak_pip="{ item }">
          <AppAutocomplete
            v-model="form.id_layak_pip[item.peserta_didik_id]"
            :items="layak_pip"
            placeholder="== Pilih =="
            item-title="alasan_layak_pip"
            item-value="id_layak_pip"
          />
        </template>
        <template #item.aksi="{ item }">
          <VBtn
            size="small"
            @click="simpan(item.peserta_didik_id)"
            :disabled="loadings[item.peserta_didik_id]"
            :loading="loadings[item.peserta_didik_id]"
            >Simpan</VBtn
          >
        </template>
      </VDataTableServer>
    </VCard>
    <VCard color="#007BB6" v-else>
      <VCardText>
        <p class="clamp-text text-white mb-0">
          Data Sekolah tidak ditemukan. Silahkan Simpan Data Sekolah terlebih dahulu
          <RouterLink class="text-success ms-1" :to="{ name: 'root' }">
            disini
          </RouterLink>
        </p>
      </VCardText>
    </VCard>
    <VSnackbar v-model="isSnackbarTopEndVisible" color="success" location="top end">
      PIP berhasil disimpan.
    </VSnackbar>
  </template>
</template>
