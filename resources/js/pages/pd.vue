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
    form.value.semester_id = getData.semester?.semester_id;
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
    title: "Tinggi Badan",
    key: "tinggi",
    sortable: false,
  },
  {
    title: "Berat Badan",
    key: "berat",
    align: "center",
    sortable: false,
  },
  {
    title: "Jarak",
    key: "jarak",
    align: "center",
    sortable: false,
  },
  {
    title: "waktu",
    key: "waktu",
    align: "center",
    sortable: false,
  },
  {
    title: "Jml Saudara Kandung",
    key: "saudara",
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
const updateSortBy = (val) => {
  options.value.sortby = val[0]?.key;
  options.value.sortbydesc = val[0]?.order;
};
const form = ref({
  semester_id: null,
  tinggi_badan: {},
  berat_badan: {},
  jarak_rumah_ke_sekolah: {},
  waktu_tempuh_ke_sekolah: {},
  menit_tempuh_ke_sekolah: {},
  jumlah_saudara_kandung: {},
});
const isSnackbarTopEndVisible = ref(false);
const fetchItem = async () => {
  loadingTable.value = true;
  try {
    const response = await useApi(
      createUrl("/periodik", {
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
    items.value.forEach((e) => {
      form.value.tinggi_badan[e.peserta_didik_id] = e.periodik
        ? e.periodik.tinggi_badan
        : null;
      form.value.berat_badan[e.peserta_didik_id] = e.periodik
        ? e.periodik.berat_badan
        : null;
      form.value.jarak_rumah_ke_sekolah[e.peserta_didik_id] = e.periodik
        ? e.periodik.jarak_rumah_ke_sekolah
        : null;
      form.value.waktu_tempuh_ke_sekolah[e.peserta_didik_id] = e.periodik
        ? e.periodik.waktu_tempuh_ke_sekolah
        : null;
      form.value.menit_tempuh_ke_sekolah[e.peserta_didik_id] = e.periodik
        ? e.periodik.menit_tempuh_ke_sekolah
        : null;
      form.value.jumlah_saudara_kandung[e.peserta_didik_id] = e.periodik
        ? e.periodik.jumlah_saudara_kandung
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
  tinggi_badan: {},
  berat_badan: {},
  jarak_rumah_ke_sekolah: {},
  waktu_tempuh_ke_sekolah: {},
  menit_tempuh_ke_sekolah: {},
  jumlah_saudara_kandung: {},
});
const simpan = async (peserta_didik_id) => {
  id.value = peserta_didik_id;
  loadings.value[peserta_didik_id] = true;
  await $api("/periodik", {
    method: "POST",
    body: {
      semester_id: form.value.semester_id,
      peserta_didik_id: peserta_didik_id,
      tinggi_badan: form.value.tinggi_badan[peserta_didik_id],
      berat_badan: form.value.berat_badan[peserta_didik_id],
      jarak_rumah_ke_sekolah: form.value.jarak_rumah_ke_sekolah[peserta_didik_id],
      waktu_tempuh_ke_sekolah: form.value.waktu_tempuh_ke_sekolah[peserta_didik_id],
      menit_tempuh_ke_sekolah: form.value.menit_tempuh_ke_sekolah[peserta_didik_id],
      jumlah_saudara_kandung: form.value.jumlah_saudara_kandung[peserta_didik_id],
    },
    async onResponse({ response }) {
      console.log(form.value.tinggi_badan[peserta_didik_id]);

      const getData = response._data;
      loadings.value[peserta_didik_id] = false;
      errors.value.tinggi_badan[peserta_didik_id] =
        getData.errors?.tinggi_badan ?? undefined;
      errors.value.berat_badan[peserta_didik_id] =
        getData.errors?.berat_badan ?? undefined;
      errors.value.jarak_rumah_ke_sekolah[peserta_didik_id] =
        getData.errors?.jarak_rumah_ke_sekolah ?? undefined;
      errors.value.waktu_tempuh_ke_sekolah[peserta_didik_id] =
        getData.errors?.waktu_tempuh_ke_sekolah ?? undefined;
      errors.value.menit_tempuh_ke_sekolah[peserta_didik_id] =
        getData.errors?.menit_tempuh_ke_sekolah ?? undefined;
      errors.value.jumlah_saudara_kandung[peserta_didik_id] =
        getData.errors?.jumlah_saudara_kandung ?? undefined;
      if (!getData.errors) {
        isSnackbarTopEndVisible.value = true;
      }
    },
  });
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
        <template #header.waktu="{ item }">
          Waktu Tempuh <br />
          Jam | Menit
        </template>
        <template #header.saudara="{ item }">
          Jml Saudara <br />
          Kandung
        </template>
        <template #item.kelas="{ item }">
          {{ item.kelas?.nama }}
        </template>
        <template #item.tinggi="{ item }">
          <AppTextField
            v-model="form.tinggi_badan[item.peserta_didik_id]"
            :error-messages="errors.tinggi_badan[item.peserta_didik_id]"
          />
        </template>
        <template #item.berat="{ item }">
          <AppTextField
            v-model="form.berat_badan[item.peserta_didik_id]"
            :error-messages="errors.berat_badan[item.peserta_didik_id]"
          />
        </template>
        <template #item.jarak="{ item }">
          <AppSelect
            :items="[
              {
                title: 'Kurang dari 1 KM',
                value: '1',
              },
              {
                title: 'Lebih dari 1 KM',
                value: '2',
              },
            ]"
            v-model="form.jarak_rumah_ke_sekolah[item.peserta_didik_id]"
            :error-messages="errors.jarak_rumah_ke_sekolah[item.peserta_didik_id]"
            style="inline-size: 10rem"
          />
        </template>
        <template #item.waktu="{ item }">
          <VRow>
            <VCol cols="12" sm="6">
              <AppTextField
                v-model="form.waktu_tempuh_ke_sekolah[item.peserta_didik_id]"
                :error-messages="errors.waktu_tempuh_ke_sekolah[item.peserta_didik_id]"
              />
            </VCol>

            <VCol cols="12" sm="6">
              <AppTextField
                v-model="form.menit_tempuh_ke_sekolah[item.peserta_didik_id]"
                :error-messages="errors.menit_tempuh_ke_sekolah[item.peserta_didik_id]"
              />
            </VCol>
          </VRow>
        </template>
        <template #item.saudara="{ item }">
          <AppTextField
            v-model="form.jumlah_saudara_kandung[item.peserta_didik_id]"
            :error-messages="errors.jumlah_saudara_kandung[item.peserta_didik_id]"
            style="inline-size: 5rem"
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
      Data Periodik berhasil disimpan.
    </VSnackbar>
  </template>
</template>
