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
    title: "NIK",
    key: "nik",
  },
  {
    title: "Induk",
    key: "induk",
    align: "center",
    sortable: false,
  },
  {
    title: "Jenis PTK",
    key: "jenis_ptk",
    sortable: false,
  },
  {
    title: "Jabatan PTK",
    key: "jabatan_ptk",
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
const jenis_ptk = ref([]);
const jabatan_ptk = ref([]);
const updateSortBy = (val) => {
  options.value.sortby = val[0]?.key;
  options.value.sortbydesc = val[0]?.order;
};
const form = ref({
  ptk_induk: {},
  jenis_ptk_id: {},
  jabatan_ptk_id: {},
});
const isSnackbarTopEndVisible = ref(false);
const fetchItem = async () => {
  loadingTable.value = true;
  try {
    const response = await useApi(
      createUrl("/guru", {
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
    jenis_ptk.value = getData.jenis_ptk;
    items.value.forEach((e) => {
      jabatan_ptk.value[e.ptk_terdaftar.ptk_terdaftar_id] = getData.jabatan;
      form.value.ptk_induk[e.ptk_terdaftar.ptk_terdaftar_id] = parseInt(
        e.ptk_terdaftar.ptk_induk
      );
      form.value.jenis_ptk_id[e.ptk_terdaftar.ptk_terdaftar_id] = parseInt(
        e.ptk_terdaftar.jenis_ptk_id
      );
      form.value.jabatan_ptk_id[e.ptk_terdaftar.ptk_terdaftar_id] = parseInt(
        e.ptk_terdaftar.jabatan_ptk_id
      );
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
const simpan = async (ptk_terdaftar_id) => {
  id.value = ptk_terdaftar_id;
  loadings.value[ptk_terdaftar_id] = true;
  await $api("/guru", {
    method: "POST",
    body: {
      ptk_terdaftar_id: ptk_terdaftar_id,
      ptk_induk: form.value.ptk_induk[ptk_terdaftar_id],
      jenis_ptk_id: form.value.jenis_ptk_id[ptk_terdaftar_id],
      jabatan_ptk_id: form.value.jabatan_ptk_id[ptk_terdaftar_id],
    },
    async onResponse() {
      loadings.value[ptk_terdaftar_id] = false;
      isSnackbarTopEndVisible.value = true;
    },
  });
  /*this.resetValidation(ptk_terdaftar_id);
  this.$http
    .post("/guru", {
      ptk_terdaftar_id: ptk_terdaftar_id,
      ptk_induk: this.form.ptk_induk[ptk_terdaftar_id],
      jabatan_ptk_id: this.form.jabatan_ptk_id[ptk_terdaftar_id],
    })
    .then((res) => {
      let getData = res.data;
      if (getData.errors) {
        this.$set(
          this.feedback.ptk_induk,
          ptk_terdaftar_id,
          getData.errors.ptk_induk ? getData.errors.ptk_induk.join(", ") : null
        );
        this.$set(
          this.feedback.jabatan_ptk_id,
          ptk_terdaftar_id,
          getData.errors.jabatan_ptk_id ? getData.errors.jabatan_ptk_id.join(", ") : null
        );
        this.state.ptk_induk[ptk_terdaftar_id] = getData.errors.ptk_induk ? false : null;
        this.state.jabatan_ptk_id[ptk_terdaftar_id] = getData.errors.jabatan_ptk_id
          ? false
          : null;
      } else {
        this.resetValidation(ptk_terdaftar_id);
        this.$bvToast.toast("Data PTK berhasil disimpan.", {
          title: `Berhasil`,
          variant: "success",
          solid: true,
        });
      }
    });*/
};
const formLoading = ref([]);
const changeJenisPtk = async (ptk_terdaftar_id, val) => {
  console.log(val);
  formLoading.value[ptk_terdaftar_id] = true;
  await $api("/get-jabatan", {
    method: "POST",
    body: {
      jenis_ptk_id: val,
    },
    async onResponse({ response }) {
      let getData = response._data;
      formLoading.value[ptk_terdaftar_id] = false;
      form.value.jabatan_ptk_id[ptk_terdaftar_id] = null;
      console.log(getData);
      jabatan_ptk.value[ptk_terdaftar_id] = getData;
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
        <VCardTitle>Data PTK</VCardTitle>
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
        <template #item.induk="{ item }">
          <AppSelect
            :items="[
              {
                title: 'Ya',
                value: 1,
              },
              {
                title: 'Tidak',
                value: 0,
              },
            ]"
            v-model="form.ptk_induk[item.ptk_terdaftar.ptk_terdaftar_id]"
          />
        </template>
        <template #item.jenis_ptk="{ item }">
          <AppAutocomplete
            v-model="form.jenis_ptk_id[item.ptk_terdaftar.ptk_terdaftar_id]"
            :items="jenis_ptk"
            placeholder="== Pilih Jenis PTK =="
            item-title="jenis_ptk"
            item-value="jenis_ptk_id"
            @update:model-value="
              changeJenisPtk(item.ptk_terdaftar.ptk_terdaftar_id, $event)
            "
          />
        </template>
        <template #item.jabatan_ptk="{ item }">
          <AppAutocomplete
            v-model="form.jabatan_ptk_id[item.ptk_terdaftar.ptk_terdaftar_id]"
            :items="jabatan_ptk[item.ptk_terdaftar.ptk_terdaftar_id]"
            placeholder="== Pilih Jabatan PTK =="
            item-title="jabatan_ptk"
            item-value="jabatan_ptk_id"
            :disabled="formLoading[item.ptk_terdaftar.ptk_terdaftar_id]"
            :loading="formLoading[item.ptk_terdaftar.ptk_terdaftar_id]"
          />
        </template>
        <template #item.aksi="{ item }">
          <VBtn
            size="small"
            @click="simpan(item.ptk_terdaftar.ptk_terdaftar_id)"
            :disabled="loadings[item.ptk_terdaftar.ptk_terdaftar_id]"
            :loading="loadings[item.ptk_terdaftar.ptk_terdaftar_id]"
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
      PTK berhasil disimpan.
    </VSnackbar>
  </template>
</template>
