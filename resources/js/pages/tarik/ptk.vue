<script setup>
import { Indonesian } from "flatpickr/dist/l10n/id.js";
const dateConfig = {
  dateFormat: `Y-m-d`,
  locale: Indonesian,
  altFormat: "j F Y",
  altInput: true,
};
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
    form.value.sekolah_id = getData.sekolah?.sekolah_id;
    form.value.tahun_ajaran_id = getData.semester.tahun_ajaran_id;
    arrayData.value.jenis_ptk = getData.jenis_ptk;
  } catch (error) {
    console.error(error);
  } finally {
    isLoading.value = false;
  }
};
const formLoading = ref(false);
const changeJenisPtk = async (val) => {
  formLoading.value = true;
  await $api("/get-jabatan", {
    method: "POST",
    body: {
      jenis_ptk_id: val,
    },
    async onResponse({ response }) {
      let getData = response._data;
      formLoading.value = false;
      form.value.jabatan_ptk_id = null;
      arrayData.value.jabatan_ptk = getData;
    },
  });
};
const nik = ref();
const errors = ref({
  nik: undefined,
  tahun_ajaran_id: undefined,
  ptk_terdaftar_id: undefined,
  sekolah_id: undefined,
  jenis_ptk_id: undefined,
  jabatan_ptk_id: undefined,
  nomor_surat_tugas: undefined,
  tanggal_surat_tugas: undefined,
  tmt_tugas: undefined,
  ptk_induk: undefined,
});
const btnLoading = ref(false);
const result = ref();
const ptk = ref();
const show = ref(false);
const arrayData = ref({
  jenis_ptk: [],
  jabatan_ptk: [],
});
const cariNik = async () => {
  errors.value.nik = undefined;
  result.value = undefined;
  ptk.value = undefined;
  show.value = false;
  if (nik.value) {
    btnLoading.value = true;
    await $api("/cari-data", {
      method: "POST",
      body: {
        data: "guru",
        nik: nik.value,
        sekolah_id: form.value.sekolah_id,
      },
      async onResponse({ response }) {
        let getData = response._data;
        show.value = true;
        result.value = getData.result;
        ptk.value = getData.ptk;
        form.value.ptk = result.value;
        btnLoading.value = false;
      },
    });
  } else {
    errors.value.nik = "NIK tidak boleh kosong";
  }
};
const jenis_keluar = (id) => {
  var data = {
    1: "Lulus",
    2: "Mutasi",
    3: "Dikeluarkan",
    4: "Mengundurkan diri",
    5: "Putus Sekolah",
    6: "Wafat",
    7: "Hilang",
    8: "Alih Fungsi",
    9: "Pensiun",
    Z: "Lainnya",
  };
  if (id) {
    return data[id];
  }
};
const refVForm = ref();
const form = ref({
  data: "guru",
  tahun_ajaran_id: null,
  ptk_terdaftar_id: null,
  sekolah_id: null,
  jenis_ptk_id: null,
  jabatan_ptk_id: null,
  nomor_surat_tugas: null,
  tanggal_surat_tugas: null,
  tmt_tugas: null,
  ptk_induk: null,
  ptk: null,
});
const onSubmit = () => {
  refVForm.value?.validate().then(({ valid: isValid }) => {
    if (isValid) submit();
  });
};
const isAlertDialogVisible = ref(false);
const notif = ref({
  color: "",
  title: "",
  text: "",
});
const submit = async () => {
  btnLoading.value = true;
  console.log("submit");
  await $api("/daftar", {
    method: "POST",
    body: form.value,
    async onResponse({ response }) {
      let getData = response._data;
      btnLoading.value = false;
      isAlertDialogVisible.value = true;
      notif.value = getData;
    },
  });
};
const confirmDialog = async () => {
  form.value.ptk_terdaftar_id = null;
  form.value.jenis_ptk_id = null;
  form.value.jabatan_ptk_id = null;
  form.value.nomor_surat_tugas = null;
  form.value.tanggal_surat_tugas = null;
  form.value.tmt_tugas = null;
  form.value.ptk_induk = null;
  ptk.value = null;
  nik.value = undefined;
  errors.value = {
    nik: undefined,
    tahun_ajaran_id: undefined,
    ptk_terdaftar_id: undefined,
    sekolah_id: undefined,
    jenis_ptk_id: undefined,
    jabatan_ptk_id: undefined,
    nomor_surat_tugas: undefined,
    tanggal_surat_tugas: undefined,
    tmt_tugas: undefined,
    ptk_induk: undefined,
  };
  result.value = undefined;
  ptk.value = undefined;
  show.value = false;
  arrayData.value.jabatan_ptk = [];
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
        <VCardTitle>Tarik PTK</VCardTitle>
      </VCardItem>
      <VDivider />
      <VCardText>
        <AppTextField
          v-model="nik"
          required
          clearable
          placeholder="Masukkan NIK"
          type="text"
          :rules="[requiredValidator]"
          :error-messages="errors.nik"
        >
          <!-- Append -->
          <template #append>
            <VBtn
              :icon="$vuetify.display.smAndDown"
              @click="cariNik"
              :disabled="btnLoading"
              :loading="btnLoading"
            >
              <VIcon icon="tabler-search" color="#fff" size="22" />
              <span v-if="$vuetify.display.mdAndUp" class="ms-3">Cari...</span>
            </VBtn>
          </template>
        </AppTextField>
      </VCardText>
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
    <template v-if="show">
      <VCard class="mt-4">
        <VCardItem class="pb-4">
          <VCardTitle>Hasil Pencarian</VCardTitle>
        </VCardItem>
        <VDivider />
        <template v-if="result">
          <VTable>
            <tbody>
              <tr>
                <td>Nama</td>
                <td>
                  {{ result.nama }}
                </td>
              </tr>
              <tr>
                <td>NIK</td>
                <td>{{ result.nik }}</td>
              </tr>
              <tr>
                <td>NUPTK</td>
                <td>{{ result.nuptk }}</td>
              </tr>
              <tr>
                <td>Email</td>
                <td>{{ result.email }}</td>
              </tr>
              <tr>
                <td>Tempat, Tanggal Lahir</td>
                <td>{{ result.tempat_lahir }}, {{ result.tanggal_lahir }}</td>
              </tr>
              <tr>
                <td>Nama Ibu Kandung</td>
                <td>{{ result.nama_ibu_kandung }}</td>
              </tr>
            </tbody>
          </VTable>
        </template>
        <template v-else> <VCardText> NIK tidak ditemukan! </VCardText> </template>
      </VCard>
      <template v-if="result">
        <VCard class="mt-4">
          <VCardItem class="pb-4">
            <VCardTitle>Riwayat PTK Terdaftar</VCardTitle>
          </VCardItem>
          <VDivider />
          <VTable>
            <thead>
              <tr>
                <th class="text-center">Nama Sekolah</th>
                <th class="text-center">NPSN</th>
                <th class="text-center">Tahun</th>
                <th class="text-center">Jenis Keluar</th>
                <th class="text-center">Tanggal Keluar</th>
              </tr>
            </thead>
            <tbody>
              <template v-if="result.terdaftar.length">
                <tr
                  v-for="terdaftar in result.terdaftar"
                  :key="terdaftar.ptk_terdaftar_id"
                >
                  <td>{{ terdaftar.sekolah.nama }}</td>
                  <td class="text-center">{{ terdaftar.sekolah.npsn }}</td>
                  <td class="text-center">{{ terdaftar.tahun_ajaran_id }}</td>
                  <td class="text-center">
                    {{ jenis_keluar(terdaftar.jenis_keluar_id) }}
                  </td>
                  <td class="text-center">{{ terdaftar.tgl_ptk_keluar }}</td>
                </tr>
              </template>
              <template v-else>
                <tr>
                  <td class="text-center" colspan="5">
                    Tidak ada data untuk ditampilkan
                  </td>
                </tr>
              </template>
            </tbody>
          </VTable>
        </VCard>
        <VCard class="mt-4">
          <VCardItem class="pb-4">
            <VCardTitle>Registrasi PTK</VCardTitle>
          </VCardItem>
          <VDivider />
          <VCardText>
            <template v-if="ptk"> PTK Telah Terdaftar </template>
            <template v-else>
              <VForm ref="refVForm" @submit.prevent="onSubmit">
                <VRow>
                  <VCol cols="12">
                    <VRow no-gutters>
                      <VCol cols="12" md="3" class="d-flex align-items-center">
                        <label
                          class="v-label text-body-2 text-high-emphasis"
                          for="jenis_ptk_id"
                          >Jenis PTK
                        </label>
                      </VCol>
                      <VCol cols="12" md="9">
                        <AppAutocomplete
                          id="jenis_ptk_id"
                          v-model="form.jenis_ptk_id"
                          :items="arrayData.jenis_ptk"
                          placeholder="== Pilih Jenis PTK =="
                          item-title="jenis_ptk"
                          item-value="jenis_ptk_id"
                          @update:model-value="changeJenisPtk"
                          :rules="[requiredValidator]"
                          :error-messages="errors.jenis_ptk_id"
                        />
                      </VCol>
                    </VRow>
                  </VCol>
                  <VCol cols="12">
                    <VRow no-gutters>
                      <VCol cols="12" md="3" class="d-flex align-items-center">
                        <label
                          class="v-label text-body-2 text-high-emphasis"
                          for="jabatan_ptk_id"
                          >Jabatan PTK</label
                        >
                      </VCol>
                      <VCol cols="12" md="9">
                        <AppAutocomplete
                          id="jabatan_ptk_id"
                          v-model="form.jabatan_ptk_id"
                          :items="arrayData.jabatan_ptk"
                          placeholder="== Pilih Jabatan PTK =="
                          item-title="jabatan_ptk"
                          item-value="jabatan_ptk_id"
                          :disabled="formLoading"
                          :loading="formLoading"
                          :rules="[requiredValidator]"
                          :error-messages="errors.jabatan_ptk_id"
                        />
                      </VCol>
                    </VRow>
                  </VCol>
                  <VCol cols="12">
                    <VRow no-gutters>
                      <VCol cols="12" md="3" class="d-flex align-items-center">
                        <label
                          class="v-label text-body-2 text-high-emphasis"
                          for="nomor_surat_tugas"
                          >Nomor Surat Tugas</label
                        >
                      </VCol>

                      <VCol cols="12" md="9">
                        <AppTextField
                          id="nomor_surat_tugas"
                          v-model="form.nomor_surat_tugas"
                          placeholder="Nomor Surat Tugas"
                          persistent-placeholder
                          :rules="[requiredValidator]"
                          :error-messages="errors.nomor_surat_tugas"
                        />
                      </VCol>
                    </VRow>
                  </VCol>
                  <VCol cols="12">
                    <VRow no-gutters>
                      <VCol cols="12" md="3" class="d-flex align-items-center">
                        <label
                          class="v-label text-body-2 text-high-emphasis"
                          for="tanggal_surat_tugas"
                          >Tanggal Surat Tugas
                        </label>
                      </VCol>

                      <VCol cols="12" md="9">
                        <AppDateTimePicker
                          id="tanggal_surat_tugas"
                          v-model="form.tanggal_surat_tugas"
                          :rules="[requiredValidator]"
                          placeholder="== Pilih Tanggal =="
                          :config="dateConfig"
                        />
                      </VCol>
                    </VRow>
                  </VCol>
                  <VCol cols="12">
                    <VRow no-gutters>
                      <VCol cols="12" md="3" class="d-flex align-items-center">
                        <label
                          class="v-label text-body-2 text-high-emphasis"
                          for="tmt_tugas"
                          >TMT Tugas
                        </label>
                      </VCol>
                      <VCol cols="12" md="9">
                        <AppDateTimePicker
                          id="tmt_tugas"
                          v-model="form.tmt_tugas"
                          :rules="[requiredValidator]"
                          placeholder="== Pilih Tanggal =="
                          :config="dateConfig"
                        />
                      </VCol>
                    </VRow>
                  </VCol>
                  <VCol cols="12">
                    <VRow no-gutters>
                      <VCol cols="12" md="3" class="d-flex align-items-center">
                        <label
                          class="v-label text-body-2 text-high-emphasis"
                          for="nomor_surat_tugas"
                          >PTK Induk</label
                        >
                      </VCol>
                      <VCol cols="12" md="9">
                        <VRadioGroup v-model="form.ptk_induk">
                          <VRadio label="Ya" :value="1" />
                          <VRadio label="Tidak" :value="0" />
                        </VRadioGroup>
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
                          :disabled="btnLoading"
                          :loading="btnLoading"
                        >
                          Simpan
                        </VBtn>
                      </VCol>
                    </VRow>
                  </VCol>
                </VRow>
              </VForm>
            </template>
          </VCardText>
        </VCard>
      </template>
    </template>
  </template>
  <AlertDialog
    v-model:isDialogVisible="isAlertDialogVisible"
    :confirm-color="notif.color"
    :confirm-title="notif.title"
    :confirm-msg="notif.text"
    @confirm="confirmDialog"
  />
</template>
