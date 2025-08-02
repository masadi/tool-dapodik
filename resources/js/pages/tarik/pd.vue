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
const jam_sinkron = ref(true);
const fetchData = async () => {
  isLoading.value = true;
  try {
    const response = await useApi(createUrl("/cek-sekolah"));
    let getData = response.data.value;
    sekolah.value = getData.sekolah;
    form.value.sekolah_id = getData.sekolah?.sekolah_id;
    form.value.tahun_ajaran_id = getData.semester?.tahun_ajaran_id;
    arrayData.value.jenis_pendaftaran = getData.jenis_pendaftaran;
    jam_sinkron.value = getData.jam_sinkron;
  } catch (error) {
    console.error(error);
  } finally {
    isLoading.value = false;
  }
};
const nik = ref();
const pd = ref();
const errors = ref({
  jenis_pendaftaran_id: null,
  tanggal_masuk_sekolah: null,
  nipd: null,
  rombongan_belajar_id: null,
});
const btnLoading = ref(false);
const result = ref();
const show = ref(false);
const arrayData = ref({
  jenis_pendaftaran: [],
  rombongan_belajar: [],
});
const cariNik = async () => {
  errors.value.nik = undefined;
  result.value = undefined;
  form.value.peserta_didik = undefined;
  show.value = false;
  pd.value = undefined;
  if (nik.value) {
    btnLoading.value = true;
    await $api("/cari-data", {
      method: "POST",
      body: {
        data: "siswa",
        nik: nik.value,
        sekolah_id: form.value.sekolah_id,
      },
      async onResponse({ response }) {
        let getData = response._data;
        show.value = true;
        pd.value = getData.pd;
        result.value = getData.result;
        /*if (!Array.isArray(result.value)) {
          form.value.peserta_didik = result.value;
        }*/
        arrayData.value.rombongan_belajar = getData.rombongan_belajar;
        //pd.value = getData.pd;
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
  data: "siswa",
  sekolah_id: null,
  nik: null,
  registrasi_id: null,
  jenis_pendaftaran_id: null,
  tanggal_masuk_sekolah: null,
  nipd: null,
  a_pernah_paud: null,
  a_pernah_tk: null,
  sekolah_asal: null,
  id_hobby: null,
  id_cita: null,
  rombongan_belajar_id: null,
  peserta_didik: null,
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
  form.value.registrasi_id = null;
  form.value.jenis_pendaftaran_id = null;
  form.value.tanggal_masuk_sekolah = null;
  form.value.nipd = null;
  form.value.a_pernah_paud = null;
  form.value.a_pernah_tk = null;
  form.value.sekolah_asal = null;
  form.value.id_hobby = null;
  form.value.id_cita = null;
  form.value.rombongan_belajar_id = null;
  form.value.peserta_didik = null;
  nik.value = undefined;
  errors.value = {
    jenis_pendaftaran_id: null,
    tanggal_masuk_sekolah: null,
    nipd: null,
    rombongan_belajar_id: null,
  };
  result.value = [];
  show.value = false;
  arrayData.value.rombongan_belajar = [];
};
const daftarPd = async (item, reg) => {
  if (reg) {
    form.value.a_pernah_paud = reg.a_pernah_paud;
    form.value.a_pernah_tk = reg.a_pernah_tk;
    form.value.sekolah_asal = reg.sekolah.nama;
    form.value.id_hobby = reg.id_hobby;
    form.value.id_cita = reg.id_cita;
  }
  await $api("/cek-pd", {
    method: "POST",
    body: {
      sekolah_id: form.value.sekolah_id,
      peserta_didik_id: item.peserta_didik_id,
      nik: nik.value,
    },
    async onResponse({ response }) {
      const getData = response._data;
      pd.value = getData.pd;
      if (Array.isArray(result.value)) {
        form.value.peserta_didik = result.value.find((pd) => {
          return pd.peserta_didik_id === item.peserta_didik_id;
        });
      } else {
        form.value.peserta_didik = result.value;
      }
    },
  });
};
const tarikPd = (registrasi_id) => {
  console.log(registrasi_id);
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
        <VCardTitle>Tarik PD</VCardTitle>
      </VCardItem>
      <VDivider />
      <VCardText>
        <template v-if="jam_sinkron">
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
        </template>
        <template v-else>
          <VAlert color="error" class="text-center" variant="tonal">
            <h2 class="mt-4 mb-4">Penyesuaian Waktu Layanan Tarik Data Dapodik</h2>
            <p>
              Dikarenakan adanya proses rutin sinkronisasi data Dapodik di Server
              PUSDATIN,
              <br />maka layanan sinkronisasi hanya dapat diakses antara pukul
              <span class="text-danger"><b>03.00 s/d 24.00 (WIB)</b></span>
            </p>
          </VAlert>
        </template>
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
          <template v-if="!Array.isArray(result)">
            <VTable>
              <tbody>
                <tr>
                  <td>Nama</td>
                  <td>{{ result.nama }}</td>
                </tr>
                <tr>
                  <td>NIK</td>
                  <td>{{ result.nik }}</td>
                </tr>
                <tr>
                  <td>NISN</td>
                  <td>{{ result.nisn }}</td>
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
          <template v-else="result.length">
            <VDataTable
              :headers="[
                { title: 'Nama', key: 'nama' },
                { title: 'NIK', key: 'nik' },
                { title: 'NISN', key: 'nisn' },
                { title: 'Tempat, Tanggal Lahir', key: 'tempat' },
                { title: 'Nama Ibu Kandung', key: 'nama_ibu_kandung' },
                { title: 'Detil', key: 'data-table-expand', align: 'end' },
              ]"
              :items="result"
              item-value="peserta_didik_id"
              show-expand
            >
              <template #item.tempat="{ item }">
                {{ item.tempat_lahir }}, {{ item.tanggal_lahir }}
              </template>
              <template
                v-slot:item.data-table-expand="{ internalItem, isExpanded, toggleExpand }"
              >
                <v-btn
                  :append-icon="
                    isExpanded(internalItem) ? 'tabler-chevron-up' : 'tabler-chevron-down'
                  "
                  :text="isExpanded(internalItem) ? 'Tutup' : 'Detil'"
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
                  <td :colspan="columns.length" class="py-2">
                    <v-sheet rounded="lg" border>
                      <v-table density="compact">
                        <tbody class="bg-surface-light">
                          <tr>
                            <th>Nama Sekolah</th>
                            <th>NPSN</th>
                            <th>Jenis Keluar</th>
                            <th>Tanggal Keluar</th>
                            <th>Daftarkan</th>
                          </tr>
                        </tbody>

                        <tbody>
                          <template v-if="item.registrasi_pd.length">
                            <tr
                              v-for="reg in item.registrasi_pd"
                              :key="reg.registrasi_id"
                            >
                              <td class="py-2">{{ reg.sekolah?.nama }}</td>
                              <td class="py-2">{{ reg.sekolah?.npsn }}</td>
                              <td class="py-2">
                                {{ jenis_keluar(reg.jenis_keluar_id) }}
                              </td>
                              <td class="py-2">{{ reg.tanggal_keluar }}</td>
                              <td class="py-2">
                                <VBtn size="small" @click="daftarPd(item, reg)"
                                  >Daftarkan</VBtn
                                >
                              </td>
                            </tr>
                          </template>
                          <template v-else>
                            <tr>
                              <td class="py-2 text-center" colspan="4">
                                Tidak data untuk ditampilan
                              </td>
                              <td>
                                <VBtn size="small" @click="daftarPd(item, null)"
                                  >Daftarkan</VBtn
                                >
                              </td>
                            </tr>
                          </template>
                        </tbody>
                      </v-table>
                    </v-sheet>
                  </td>
                </tr>
              </template>
            </VDataTable>
          </template>
        </template>
        <template v-else> <VCardText> NIK tidak ditemukan! </VCardText> </template>
      </VCard>
      <VCard class="mt-4" v-if="result && !Array.isArray(result)">
        <VCardItem class="pb-4">
          <VCardTitle>Riwayat Pendidikan Siswa</VCardTitle>
        </VCardItem>
        <VDivider />
        <VTable>
          <thead>
            <tr>
              <th>Nama Sekolah</th>
              <th class="text-center">NPSN</th>
              <th class="text-center">Jenis Keluar</th>
              <th class="text-center">Tanggal Keluar</th>
              <th class="text-center">Daftarkan</th>
            </tr>
          </thead>
          <tbody>
            <template v-if="result.registrasi_pd.length">
              <tr v-for="(reg, index) in result.registrasi_pd" :key="reg.registrasi_id">
                <td>
                  {{ reg.sekolah.nama }}
                  <a
                    href="javascript:void(0)"
                    @click="tarikPd(reg.registrasi_id)"
                    v-if="reg.sekolah_id.toLowerCase() === form.sekolah_id"
                    ><VIcon icon="tabler-clipboard-check"></VIcon
                  ></a>
                </td>
                <td class="text-center">{{ reg.sekolah.npsn }}</td>
                <td class="text-center">{{ jenis_keluar(reg.jenis_keluar_id) }}</td>
                <td class="text-center">{{ reg.tanggal_keluar }}</td>
                <td class="py-2">
                  <VBtn size="small" @click="daftarPd(result, reg)">Daftarkan</VBtn>
                </td>
              </tr>
            </template>
            <template v-else>
              <tr>
                <td colspan="4" class="py-4 text-center">
                  Tidak ada data untuk ditampilkan
                </td>
              </tr>
            </template>
          </tbody>
        </VTable>
      </VCard>
      <VCard class="mt-4" v-if="form.peserta_didik">
        <VCardItem class="pb-4">
          <VCardTitle>Daftarkan Peserta Didik</VCardTitle>
        </VCardItem>
        <VDivider />
        <VCardText>
          <template v-if="pd">
            <VAlert color="error">
              Peserta Didik atas nama {{ pd.nama }} telah terdaftar
              <span v-if="pd.kelas">di Rombongan Belajar {{ pd.kelas.nama }}</span>
            </VAlert>
          </template>
          <template v-else>
            <VForm ref="refVForm" @submit.prevent="onSubmit">
              <VRow>
                <VCol cols="12">
                  <VRow no-gutters>
                    <VCol cols="12" md="3" class="d-flex align-items-center">
                      <label
                        class="v-label text-body-2 text-high-emphasis"
                        for="jenis_pendaftaran_id"
                        >Jenis Pendaftaran
                      </label>
                    </VCol>
                    <VCol cols="12" md="9">
                      <AppAutocomplete
                        id="jenis_ptk_id"
                        v-model="form.jenis_pendaftaran_id"
                        :items="arrayData.jenis_pendaftaran"
                        placeholder="== Pilih Jenis Pendaftaran =="
                        item-title="nama"
                        item-value="jenis_pendaftaran_id"
                        :rules="[requiredValidator]"
                        :error-messages="errors.jenis_pendaftaran_id"
                      />
                    </VCol>
                  </VRow>
                </VCol>
                <VCol cols="12">
                  <VRow no-gutters>
                    <VCol cols="12" md="3" class="d-flex align-items-center">
                      <label
                        class="v-label text-body-2 text-high-emphasis"
                        for="tanggal_masuk_sekolah"
                        >Tanggal Masuk Sekolah
                      </label>
                    </VCol>
                    <VCol cols="12" md="9">
                      <AppDateTimePicker
                        id="tanggal_masuk_sekolah"
                        v-model="form.tanggal_masuk_sekolah"
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
                      <label class="v-label text-body-2 text-high-emphasis" for="nipd"
                        >Nomor Induk Siswa</label
                      >
                    </VCol>
                    <VCol cols="12" md="9">
                      <AppTextField
                        id="nipd"
                        v-model="form.nipd"
                        placeholder="Nomor Induk Siswa"
                        persistent-placeholder
                        :rules="[requiredValidator]"
                        :error-messages="errors.nipd"
                      />
                    </VCol>
                  </VRow>
                </VCol>
                <VCol cols="12">
                  <VRow no-gutters>
                    <VCol cols="12" md="3" class="d-flex align-items-center">
                      <label
                        class="v-label text-body-2 text-high-emphasis"
                        for="rombongan_belajar_id"
                        >Rombongan Belajar</label
                      >
                    </VCol>
                    <VCol cols="12" md="9">
                      <AppAutocomplete
                        id="rombongan_belajar_id"
                        v-model="form.rombongan_belajar_id"
                        :items="arrayData.rombongan_belajar"
                        placeholder="== Pilih Rombongan Belajar =="
                        item-title="nama"
                        item-value="rombongan_belajar_id"
                        :rules="[requiredValidator]"
                        :error-messages="errors.rombongan_belajar_id"
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
  <AlertDialog
    v-model:isDialogVisible="isAlertDialogVisible"
    :confirm-color="notif.color"
    :confirm-title="notif.title"
    :confirm-msg="notif.text"
    @confirm="confirmDialog"
  />
</template>
