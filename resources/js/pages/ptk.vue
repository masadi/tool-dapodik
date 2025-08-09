<script setup>
import { useDebounceFn } from "@vueuse/core";
import { Indonesian } from "flatpickr/dist/l10n/id.js";
const dateConfig = ref({
  locale: Indonesian,
  altFormat: "j F Y",
  altInput: true,
});
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
  { title: "Menu", width: 1, key: "data-table-expand", align: "center" },
  {
    title: "Nama",
    key: "nama",
    sortable: true,
    nowrap: true,
  },
  {
    title: "Tmp. Lahir",
    key: "tempat_lahir",
    sortable: true,
    nowrap: true,
  },
  {
    title: "Tgl. Lahir",
    key: "tanggal_lahir",
    sortable: true,
    nowrap: true,
  },
  {
    title: "Nama Ibu Kandung",
    key: "nama_ibu_kandung",
    sortable: true,
    nowrap: true,
  },
  /*{
    title: "Status Kepegawaian",
    key: "status_kepegawaian_id",
  },
  {
    title: "sk pengangkatan",
    key: "sk_pengangkatan",
  },
  {
    title: "tmt pengangkatan",
    key: "tmt_pengangkatan",
  },
  {
    title: "lembaga pengangkat",
    key: "lembaga_pengangkat_id",
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
    title: "nomor surat tugas",
    key: "nomor_surat_tugas",
  },
  {
    title: "tanggal surat tugas",
    key: "tanggal_surat_tugas",
  },
  {
    title: "tmt tugas",
    key: "tmt_tugas",
  },
  {
    title: "Induk",
    key: "induk",
    align: "center",
    sortable: false,
  },
  {
    title: "aksi",
    key: "aksi",
    align: "center",
    sortable: false,
  },*/
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
const jabatan_tugas = ref([]);
const status_kepegawaian = ref([]);
const lembaga_pengangkat = ref([]);
const updateSortBy = (val) => {
  options.value.sortby = val[0]?.key;
  options.value.sortbydesc = val[0]?.order;
};
const form = ref({
  status_kepegawaian_id: {},
  lembaga_pengangkat_id: {},
  tmt_tugas: {},
  sk_pengangkatan: {},
  tmt_pengangkatan: {},
  jenis_ptk_id: {},
  jabatan_ptk_id: {},
  nomor_surat_tugas: {},
  tanggal_surat_tugas: {},
  tmt_tugas: {},
  ptk_induk: {},
  tambahan: {
    ptk_tugas_tambahan_id: [],
    jabatan_ptk_id: [],
    nomor_sk: [],
    tmt_tambahan: [],
    tst_tambahan: [],
  },
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
    jabatan_tugas.value = getData.jabatan_tugas;
    status_kepegawaian.value = getData.status_kepegawaian;
    lembaga_pengangkat.value = getData.lembaga_pengangkat;
    items.value.forEach((e) => {
      jabatan_ptk.value[e.ptk_terdaftar.ptk_terdaftar_id] = getData.jabatan;
      const tt = [];
      e.tugas_tambahan.forEach((t) => {
        tt.push({
          ptk_tugas_tambahan_id: t.ptk_tugas_tambahan_id,
          jabatan_ptk_id: t.jabatan_ptk_id,
          nomor_sk: t.nomor_sk,
          tmt_tambahan: t.tmt_tambahan,
          tst_tambahan: t.tst_tambahan,
        });
      });
      form.value.tambahan[e.ptk_id] = tt;
      form.value.status_kepegawaian_id[e.ptk_id] = parseInt(e.status_kepegawaian_id);
      form.value.sk_pengangkatan[e.ptk_id] = e.sk_pengangkatan;
      form.value.tmt_pengangkatan[e.ptk_id] = e.tmt_pengangkatan;
      form.value.lembaga_pengangkat_id[e.ptk_id] = parseInt(e.lembaga_pengangkat_id);
      form.value.jenis_ptk_id[e.ptk_terdaftar.ptk_terdaftar_id] = parseInt(
        e.ptk_terdaftar.jenis_ptk_id
      );
      form.value.jabatan_ptk_id[e.ptk_terdaftar.ptk_terdaftar_id] = parseInt(
        e.ptk_terdaftar.jabatan_ptk_id
      );
      form.value.nomor_surat_tugas[e.ptk_terdaftar.ptk_terdaftar_id] =
        e.ptk_terdaftar.nomor_surat_tugas;
      form.value.tanggal_surat_tugas[e.ptk_terdaftar.ptk_terdaftar_id] =
        e.ptk_terdaftar.tanggal_surat_tugas;
      form.value.tmt_tugas[e.ptk_terdaftar.ptk_terdaftar_id] = e.ptk_terdaftar.tmt_tugas;
      form.value.ptk_induk[e.ptk_terdaftar.ptk_terdaftar_id] = parseInt(
        e.ptk_terdaftar.ptk_induk
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
const simpanTambahan = async (ptk) => {
  console.log(ptk);
  console.log(form.value.tambahan[ptk.ptk_id]);
  loadings.value[ptk.ptk_id] = true;
  await $api("/tt", {
    method: "POST",
    body: {
      ptk_id: ptk.ptk_id,
      tambahan: form.value.tambahan[ptk.ptk_id],
    },
    async onResponse() {
      loadings.value[ptk.ptk_id] = false;
      isSnackbarTopEndVisible.value = true;
    },
  });
};
const simpan = async (ptk) => {
  loadings.value[ptk.ptk_id] = true;
  await $api("/guru", {
    method: "POST",
    body: {
      ptk_id: ptk.ptk_id,
      ptk_terdaftar_id: ptk.ptk_terdaftar.ptk_terdaftar_id,
      status_kepegawaian_id: form.value.status_kepegawaian_id[ptk.ptk_id],
      lembaga_pengangkat_id: form.value.lembaga_pengangkat_id[ptk.ptk_id],
      tmt_tugas: form.value.tmt_tugas[ptk.ptk_id],
      sk_pengangkatan: form.value.sk_pengangkatan[ptk.ptk_id],
      tmt_pengangkatan: form.value.tmt_pengangkatan[ptk.ptk_id],
      //jenis_ptk_id: {},
      //jabatan_ptk_id: {},
      //ptk_induk: {},
      jenis_ptk_id: form.value.jenis_ptk_id[ptk.ptk_terdaftar.ptk_terdaftar_id],
      jabatan_ptk_id: form.value.jabatan_ptk_id[ptk.ptk_terdaftar.ptk_terdaftar_id],
      ptk_induk: form.value.ptk_induk[ptk.ptk_terdaftar.ptk_terdaftar_id],
      nomor_surat_tugas: form.value.nomor_surat_tugas[ptk.ptk_terdaftar.ptk_terdaftar_id],
      tanggal_surat_tugas:
        form.value.tanggal_surat_tugas[ptk.ptk_terdaftar.ptk_terdaftar_id],
      tmt_tugas: form.value.tmt_tugas[ptk.ptk_terdaftar.ptk_terdaftar_id],
    },
    async onResponse() {
      loadings.value[ptk.ptk_id] = false;
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
const currentTab = ref({});
const formBio = ref();
const onSubmit = (aksi, ptk) => {
  formBio.value?.validate().then(({ valid: isValid }) => {
    if (isValid) postData(aksi, ptk);
  });
};
const postData = async (aksi, ptk) => {
  loadings.value[ptk.ptk_id] = true;
  console.log(aksi);
  await $api("/guru", {
    method: "POST",
    body: {
      aksi: aksi,
      ptk_id: ptk.ptk_id,
      ptk_terdaftar_id: ptk.ptk_terdaftar.ptk_terdaftar_id,
      status_kepegawaian_id: form.value.status_kepegawaian_id[ptk.ptk_id],
      lembaga_pengangkat_id: form.value.lembaga_pengangkat_id[ptk.ptk_id],
      tmt_tugas: form.value.tmt_tugas[ptk.ptk_id],
      sk_pengangkatan: form.value.sk_pengangkatan[ptk.ptk_id],
      tmt_pengangkatan: form.value.tmt_pengangkatan[ptk.ptk_id],
      //jenis_ptk_id: {},
      //jabatan_ptk_id: {},
      //ptk_induk: {},
      jenis_ptk_id: form.value.jenis_ptk_id[ptk.ptk_terdaftar.ptk_terdaftar_id],
      jabatan_ptk_id: form.value.jabatan_ptk_id[ptk.ptk_terdaftar.ptk_terdaftar_id],
      ptk_induk: form.value.ptk_induk[ptk.ptk_terdaftar.ptk_terdaftar_id],
      nomor_surat_tugas: form.value.nomor_surat_tugas[ptk.ptk_terdaftar.ptk_terdaftar_id],
      tanggal_surat_tugas:
        form.value.tanggal_surat_tugas[ptk.ptk_terdaftar.ptk_terdaftar_id],
      tmt_tugas: form.value.tmt_tugas[ptk.ptk_terdaftar.ptk_terdaftar_id],
    },
    async onResponse() {
      loadings.value[ptk.ptk_id] = false;
      isSnackbarTopEndVisible.value = true;
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
        item-value="ptk_id"
        :server-items-length="total"
        :items-length="total"
        :headers="headers"
        items-per-page-text="Item"
        :options="options"
        :loading="loadingTable"
        loading-text="Loading..."
        @update:sortBy="updateSortBy"
        :header-props="{ class: 'text-no-wrap' }"
        show-expand
      >
        <!--template v-slot:headers="{}" hide-default-header>
          <tr>
            <th class="text-center" rowspan="2">Menu</th>
            <th class="text-center" colspan="5">Biodata</th>
            <th
              class="text-center"
              colspan="6"
              style="
                border-left: thin solid
                  rgba(var(--v-border-color), var(--v-border-opacity));
              "
            >
              Penugasan
            </th>
            <th
              class="text-center"
              rowspan="2"
              style="
                border-left: thin solid
                  rgba(var(--v-border-color), var(--v-border-opacity));
              "
            >
              aksi
            </th>
          </tr>
          <tr>
            <th class="text-center">Nama</th>
            <th class="text-center">Status Kepegawaian</th>
            <th class="text-center">sk pengangkatan</th>
            <th class="text-center">tmt pengangkatan</th>
            <th class="text-center">lembaga pengangkat</th>
            <th class="text-center">Jenis PTK</th>
            <th class="text-center">Jabatan PTK</th>
            <th class="text-center">nomor surat tugas</th>
            <th class="text-center">tanggal surat tugas</th>
            <th class="text-center">tmt tugas</th>
            <th
              class="text-center"
              style="
                border-left: thin solid
                  rgba(var(--v-border-color), var(--v-border-opacity));
              "
            >
              Induk
            </th>
          </tr>
        </template-->
        <template
          v-slot:item.data-table-expand="{ internalItem, isExpanded, toggleExpand }"
        >
          <v-btn
            :append-icon="
              isExpanded(internalItem) ? 'mdi-chevron-up' : 'mdi-chevron-down'
            "
            :text="isExpanded(internalItem) ? 'Tutup' : 'Buka'"
            class="text-none"
            color="medium-emphasis"
            size="small"
            variant="text"
            width="105"
            border
            slim
            @click="toggleExpand(internalItem)"
          ></v-btn>
        </template>
        <template v-slot:expanded-row="{ columns, item }">
          <tr>
            <td colspan="5">
              <VTabs v-model="currentTab[item.ptk_id]" grow stacked>
                <VTab>
                  <VIcon icon="tabler-user" class="mb-2" />
                  <span>Biodata</span>
                </VTab>

                <VTab>
                  <VIcon icon="tabler-file-description" class="mb-2" />
                  <span>Penugasan</span>
                </VTab>

                <VTab>
                  <VIcon icon="tabler-briefcase" class="mb-2" />
                  <span>Tugas Tambahan</span>
                </VTab>
              </VTabs>
              <VWindow v-model="currentTab[item.ptk_id]">
                <VWindowItem :value="`${item.ptk_id}`">
                  <VForm ref="formBio" @submit.prevent="onSubmit('bio', item)">
                    <VRow class="my-4">
                      <VCol cols="12">
                        <VRow no-gutters>
                          <VCol cols="12" md="3" class="d-flex align-items-center">
                            <label class="v-label text-body-2 text-high-emphasis"
                              >Status Kepegawaian</label
                            >
                          </VCol>
                          <VCol cols="12" md="9">
                            <AppAutocomplete
                              v-model="form.status_kepegawaian_id[item.ptk_id]"
                              :items="status_kepegawaian"
                              placeholder="== Pilih Status =="
                              item-title="nama"
                              item-value="status_kepegawaian_id"
                            />
                          </VCol>
                        </VRow>
                      </VCol>
                      <VCol cols="12">
                        <VRow no-gutters>
                          <VCol cols="12" md="3" class="d-flex align-items-center">
                            <label class="v-label text-body-2 text-high-emphasis"
                              >SK Pengangkatan</label
                            >
                          </VCol>
                          <VCol cols="12" md="9">
                            <AppTextField
                              v-model="form.sk_pengangkatan[item.ptk_id]"
                              placeholder="SK Pengangkatan"
                            />
                          </VCol>
                        </VRow>
                      </VCol>
                      <VCol cols="12">
                        <VRow no-gutters>
                          <VCol cols="12" md="3" class="d-flex align-items-center">
                            <label class="v-label text-body-2 text-high-emphasis"
                              >TMT Pengangkatan</label
                            >
                          </VCol>
                          <VCol cols="12" md="9">
                            <AppDateTimePicker
                              v-model="form.tmt_pengangkatan[item.ptk_id]"
                              placeholder="== Pilih Tanggal =="
                              :config="dateConfig"
                            />
                          </VCol>
                        </VRow>
                      </VCol>
                      <VCol cols="12">
                        <VRow no-gutters>
                          <VCol cols="12" md="3" class="d-flex align-items-center">
                            <label class="v-label text-body-2 text-high-emphasis"
                              >Lembaga Pengangkat</label
                            >
                          </VCol>
                          <VCol cols="12" md="9">
                            <AppAutocomplete
                              v-model="form.lembaga_pengangkat_id[item.ptk_id]"
                              :items="lembaga_pengangkat"
                              placeholder="== Pilih Lembaga =="
                              item-title="nama"
                              item-value="lembaga_pengangkat_id"
                            />
                          </VCol>
                        </VRow>
                      </VCol>
                      <VCol cols="12">
                        <VRow no-gutters>
                          <VCol cols="12" md="3" />
                          <VCol cols="12" md="9">
                            <VBtn
                              type="submit"
                              class="me-4"
                              :disabled="loadings[item.ptk_id]"
                              :loading="loadings[item.ptk_id]"
                            >
                              Simpan
                            </VBtn>
                          </VCol>
                        </VRow>
                      </VCol>
                    </VRow>
                  </VForm>
                </VWindowItem>
                <VWindowItem :value="`${item.ptk_id}`">
                  <VForm ref="formBio" @submit.prevent="onSubmit('tugas', item)">
                    <VRow class="my-4">
                      <VCol cols="12">
                        <VRow no-gutters>
                          <VCol cols="12" md="3" class="d-flex align-items-center">
                            <label class="v-label text-body-2 text-high-emphasis"
                              >Jenis PTK</label
                            >
                          </VCol>
                          <VCol cols="12" md="9">
                            <AppAutocomplete
                              v-model="
                                form.jenis_ptk_id[item.ptk_terdaftar.ptk_terdaftar_id]
                              "
                              :items="jenis_ptk"
                              placeholder="== Pilih Jenis PTK =="
                              item-title="jenis_ptk"
                              item-value="jenis_ptk_id"
                              @update:model-value="
                                changeJenisPtk(
                                  item.ptk_terdaftar.ptk_terdaftar_id,
                                  $event
                                )
                              "
                            />
                          </VCol>
                        </VRow>
                      </VCol>
                      <VCol cols="12">
                        <VRow no-gutters>
                          <VCol cols="12" md="3" class="d-flex align-items-center">
                            <label class="v-label text-body-2 text-high-emphasis"
                              >Jabatan PTK</label
                            >
                          </VCol>
                          <VCol cols="12" md="9">
                            <AppAutocomplete
                              v-model="
                                form.jabatan_ptk_id[item.ptk_terdaftar.ptk_terdaftar_id]
                              "
                              :items="jabatan_ptk[item.ptk_terdaftar.ptk_terdaftar_id]"
                              placeholder="== Pilih Jabatan PTK =="
                              item-title="jabatan_ptk"
                              item-value="jabatan_ptk_id"
                              :disabled="formLoading[item.ptk_terdaftar.ptk_terdaftar_id]"
                              :loading="formLoading[item.ptk_terdaftar.ptk_terdaftar_id]"
                            />
                          </VCol>
                        </VRow>
                      </VCol>
                      <VCol cols="12">
                        <VRow no-gutters>
                          <VCol cols="12" md="3" class="d-flex align-items-center">
                            <label class="v-label text-body-2 text-high-emphasis"
                              >Nomor Surat Tugas</label
                            >
                          </VCol>
                          <VCol cols="12" md="9">
                            <AppTextField
                              v-model="
                                form.nomor_surat_tugas[
                                  item.ptk_terdaftar.ptk_terdaftar_id
                                ]
                              "
                              placeholder="Nomor Surat Tugas"
                            />
                          </VCol>
                        </VRow>
                      </VCol>
                      <VCol cols="12">
                        <VRow no-gutters>
                          <VCol cols="12" md="3" class="d-flex align-items-center">
                            <label class="v-label text-body-2 text-high-emphasis"
                              >Tanggal Surat Tugas</label
                            >
                          </VCol>
                          <VCol cols="12" md="9">
                            <AppDateTimePicker
                              v-model="
                                form.tanggal_surat_tugas[
                                  item.ptk_terdaftar.ptk_terdaftar_id
                                ]
                              "
                              placeholder="== Pilih Tanggal =="
                              :config="dateConfig"
                            />
                          </VCol>
                        </VRow>
                      </VCol>
                      <VCol cols="12">
                        <VRow no-gutters>
                          <VCol cols="12" md="3" class="d-flex align-items-center">
                            <label class="v-label text-body-2 text-high-emphasis"
                              >TMT Tugas</label
                            >
                          </VCol>
                          <VCol cols="12" md="9">
                            <AppDateTimePicker
                              v-model="
                                form.tmt_tugas[item.ptk_terdaftar.ptk_terdaftar_id]
                              "
                              placeholder="== Pilih Tanggal =="
                              :config="dateConfig"
                            />
                          </VCol>
                        </VRow>
                      </VCol>
                      <VCol cols="12">
                        <VRow no-gutters>
                          <VCol cols="12" md="3" class="d-flex align-items-center">
                            <label class="v-label text-body-2 text-high-emphasis"
                              >Induk</label
                            >
                          </VCol>
                          <VCol cols="12" md="9">
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
                              v-model="
                                form.ptk_induk[item.ptk_terdaftar.ptk_terdaftar_id]
                              "
                            />
                          </VCol>
                        </VRow>
                      </VCol>
                      <VCol cols="12">
                        <VRow no-gutters>
                          <VCol cols="12" md="3" />
                          <VCol cols="12" md="9">
                            <VBtn
                              type="submit"
                              class="me-4"
                              :disabled="loadings[item.ptk_id]"
                              :loading="loadings[item.ptk_id]"
                            >
                              Simpan
                            </VBtn>
                          </VCol>
                        </VRow>
                      </VCol>
                    </VRow>
                  </VForm>
                </VWindowItem>
                <VWindowItem :value="`${item.ptk_id}`">
                  <VTable class="mb-4">
                    <thead>
                      <tr>
                        <th class="text-center">Jabatan PTK</th>
                        <th class="text-center">Nomor SK</th>
                        <th class="text-center">TM Tambahan</th>
                        <th class="text-center">TST Tambahan</th>
                        <th class="text-center">Aksi</th>
                      </tr>
                    </thead>
                    <tbody>
                      <template v-if="item.tugas_tambahan.length">
                        <tr
                          v-for="(tambahan, index) in item.tugas_tambahan"
                          :key="tambahan.ptk_tugas_tambahan_id"
                        >
                          <td>
                            <AppAutocomplete
                              v-model="form.tambahan[item.ptk_id][index].jabatan_ptk_id"
                              :items="jabatan_tugas"
                              placeholder="== Pilih Jabatan PTK =="
                              item-title="nama"
                              item-value="jabatan_ptk_id"
                            />
                          </td>
                          <td>
                            <AppTextField
                              v-model="form.tambahan[item.ptk_id][index].nomor_sk"
                              placeholder="Nomor SK"
                            />
                          </td>
                          <td>
                            <AppDateTimePicker
                              v-model="form.tambahan[item.ptk_id][index].tmt_tambahan"
                              placeholder="== Pilih Tanggal =="
                              :config="dateConfig"
                            />
                          </td>
                          <td>
                            <AppDateTimePicker
                              v-model="form.tambahan[item.ptk_id][index].tst_tambahan"
                              placeholder="== Pilih Tanggal =="
                              :config="dateConfig"
                            />
                          </td>
                          <td class="text-center">
                            <VBtn
                              size="small"
                              @click="simpanTambahan(item)"
                              :disabled="loadings[item.ptk_id]"
                              :loading="loadings[item.ptk_id]"
                              >Simpan</VBtn
                            >
                          </td>
                        </tr>
                      </template>
                      <template v-else>
                        <td class="text-center" colspan="5">
                          Tidak ada untuk ditampilkan
                        </td>
                      </template>
                    </tbody>
                  </VTable>
                </VWindowItem>
              </VWindow>
            </td>
          </tr>
        </template>
        <template #item.nama="{ item }">
          <div class="d-flex align-center gap-x-4">
            <div class="d-flex flex-column">
              <h6 class="text-base">{{ item.nama }}</h6>
              <div class="text-sm">
                {{ item.nik }}
              </div>
            </div>
          </div>
        </template>
        <!--template #item.status_kepegawaian_id="{ item }">
          <AppAutocomplete
            v-model="form.status_kepegawaian_id[item.ptk_id]"
            :items="status_kepegawaian"
            placeholder="== Pilih Status =="
            item-title="nama"
            item-value="status_kepegawaian_id"
            style="inline-size: 10rem"
          />
        </template>
        <template #item.sk_pengangkatan="{ item }">
          <AppTextField
            v-model="form.sk_pengangkatan[item.ptk_id]"
            placeholder="SK Pengangkatan"
            style="inline-size: 15.625rem"
          />
        </template>
        <template #item.tmt_pengangkatan="{ item }">
          <AppDateTimePicker
            style="inline-size: 9rem"
            v-model="form.tmt_pengangkatan[item.ptk_id]"
            placeholder="== Pilih Tanggal =="
            :config="dateConfig"
          />
        </template>
        <template #item.lembaga_pengangkat_id="{ item }">
          <AppAutocomplete
            v-model="form.lembaga_pengangkat_id[item.ptk_id]"
            :items="lembaga_pengangkat"
            placeholder="== Pilih Lembaga =="
            item-title="nama"
            item-value="lembaga_pengangkat_id"
            style="inline-size: 13rem"
          />
        </template-->
        <template #item.jenis_ptk="{ item }"> </template>
        <template #item.jabatan_ptk="{ item }"> </template>
        <template #item.nomor_surat_tugas="{ item }"> </template>
        <template #item.tanggal_surat_tugas="{ item }"> </template>
        <template #item.tmt_tugas="{ item }"> </template>
        <template #item.induk="{ item }"> </template>
        <template #item.aksi="{ item }">
          <VBtn
            size="small"
            @click="simpan(item)"
            :disabled="loadings[item.ptk_id]"
            :loading="loadings[item.ptk_id]"
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
