<script setup>
import { Indonesian } from "flatpickr/dist/l10n/id.js";
definePage({
  meta: {
    action: "read",
    subject: "Ptk",
  },
});
onMounted(async () => {
  await fetchData();
});
const isLoading = ref(false);
const jam_sinkron = ref(false);
const fetchData = async () => {
  isLoading.value = true;
  try {
    const response = await useApi(
      createUrl("/cek-sekolah", {
        query: {
          ptk: 1,
        },
      })
    );
    let getData = response.data.value;
    jam_sinkron.value = getData.jam_sinkron;
    const provinsi_id = fields.value.find((s) => {
      return s.id === "provinsi_id";
    });
    provinsi_id.items = getData.wilayah;
  } catch (error) {
    console.error(error);
  } finally {
    isLoading.value = false;
  }
};
const dateConfig = {
  dateFormat: `Y-m-d`,
  locale: Indonesian,
  altFormat: "j F Y",
  altInput: true,
};
const isSnackbarTopEndVisible = ref(false);
const refVForm = ref();
const form = ref({
  nama: null,
  nik: null,
  jenis_kelamin: null,
  tempat_lahir: null,
  tanggal_lahir: null,
  provinsi_id: null,
  kabupaten_id: null,
  kecamatan_id: null,
  rt: null,
  rw: null,
  alamat_jalan: null,
  desa_kelurahan: null,
  email: null,
  nama_ibu_kandung: null,
  status_perkawinan: null,
  sk_pengangkatan: null,
  tmt_pengangkatan: null,
});
const fields = ref([
  {
    id: "nama",
    nama: "Nama",
    type: "text",
  },
  {
    id: "nik",
    nama: "NIK",
    type: "text",
  },
  {
    id: "jenis_kelamin",
    nama: "Jenis Kelamin",
    type: "select",
    items: [
      { title: "Laki-laki", value: "L" },
      { title: "Perempuan", value: "P" },
    ],
  },
  {
    id: "tempat_lahir",
    nama: "Tempat Lahir",
    type: "text",
  },
  {
    id: "tanggal_lahir",
    nama: "Tanggal Lahir",
    type: "date",
  },
  {
    id: "provinsi_id",
    nama: "Provinsi",
    type: "wilayah",
    items: [],
  },
  {
    id: "kabupaten_id",
    nama: "Kabupaten/Kota",
    type: "wilayah",
    items: [],
  },
  {
    id: "kecamatan_id",
    nama: "Kecamatan",
    type: "wilayah",
    items: [],
  },
  {
    id: "rt_rw",
    nama: "RT/RW",
    type: "rt_rw",
  },
  {
    id: "alamat_jalan",
    nama: "Alamat",
    type: "text",
  },
  {
    id: "desa_kelurahan",
    nama: "Desa/Kelurahan",
    type: "text",
  },
  {
    id: "email",
    nama: "Email",
    type: "text",
  },
  {
    id: "nama_ibu_kandung",
    nama: "Nama Ibu Kandung",
    type: "text",
  },
  {
    id: "status_perkawinan",
    nama: "Status Perkawinan",
    type: "select",
    items: [
      { title: "Belum", value: 1 },
      { title: "Sudah", value: 2 },
    ],
  },
  {
    id: "sk_pengangkatan",
    nama: "SK Pengangkatan",
    type: "text",
  },
  {
    id: "tmt_pengangkatan",
    nama: "TMT",
    type: "date",
  },
]);
const errors = ref({
  nik: undefined,
});
const submit = async () => {
  try {
    const res = await $api("/tambah-ptk", {
      method: "POST",
      body: form.value,
      onResponseError({ response }) {
        errors.value = response._data.errors;
      },
    });
    console.log(res);
    isSnackbarTopEndVisible.value = true;
    await nextTick(() => {
      form.value.tanggal_lahir = null;
      form.value.tmt_pengangkatan = null;
      refVForm.value.reset();
      console.log("reset Form");
    });
  } catch (err) {
    console.error(err);
  }
};
const onSubmit = () => {
  refVForm.value?.validate().then(({ valid: isValid }) => {
    if (isValid) submit();
  });
};
const changeWilayah = async (field, val) => {
  let cari;
  if (field == "provinsi_id") {
    cari = "kabupaten";
  } else {
    cari = "kecamatan";
  }
  await $api("/wilayah", {
    method: "POST",
    body: {
      cari: cari,
      kode_wilayah: val,
    },
    onResponseError({ response }) {
      errors.value = response._data.errors;
    },
    onResponse({ response }) {
      let getData = response._data;
      if (getData.length) {
        if (field == "provinsi_id") {
          const kabupaten_id = fields.value.find((s) => {
            return s.id === "kabupaten_id";
          });
          kabupaten_id.items = getData;
        } else {
          const kecamatan_id = fields.value.find((s) => {
            return s.id === "kecamatan_id";
          });
          kecamatan_id.items = getData;
        }
      }
    },
  });
};
</script>
<template>
  <VCard>
    <VCardItem class="pb-4">
      <VCardTitle>Tambah PTK</VCardTitle>
    </VCardItem>
    <VDivider />
    <VForm ref="refVForm" @submit.prevent="onSubmit">
      <VCardText>
        <template v-if="jam_sinkron">
          <JamSinkron />
        </template>
        <template v-else>
          <VRow>
            <template v-for="field in fields">
              <VCol cols="12">
                <VRow no-gutters>
                  <VCol cols="12" md="3" class="d-flex align-items-center">
                    <label
                      class="v-label text-body-2 text-high-emphasis"
                      :for="field.id"
                      >{{ field.nama }}</label
                    >
                  </VCol>
                  <VCol cols="12" md="9">
                    <template v-if="field.type == 'text'">
                      <AppTextField
                        :id="field.id"
                        v-model="form[field.id]"
                        :rules="[requiredValidator]"
                        :placeholder="field.nama"
                        :error-messages="errors[field.id]"
                        persistent-placeholder
                      />
                    </template>
                    <template v-if="field.type == 'date'">
                      <AppDateTimePicker
                        :id="field.id"
                        v-model="form[field.id]"
                        :rules="[requiredValidator]"
                        placeholder="== Pilih Tanggal =="
                        :config="dateConfig"
                      />
                    </template>
                    <template v-if="field.type == 'rt_rw'">
                      <VRow no-gutters>
                        <VCol cols="6">
                          <AppTextField
                            :id="field.id"
                            v-model="form.rt"
                            :rules="[requiredValidator]"
                            placeholder="RT"
                            persistent-placeholder
                            class="me-1"
                          />
                        </VCol>
                        <VCol cols="6">
                          <AppTextField
                            :id="field.id"
                            v-model="form.rw"
                            :rules="[requiredValidator]"
                            placeholder="RW"
                            persistent-placeholder
                            class="ms-1"
                          />
                        </VCol>
                      </VRow>
                    </template>
                    <template v-if="field.type == 'select'">
                      <AppSelect
                        :id="field.id"
                        v-model="form[field.id]"
                        :rules="[requiredValidator]"
                        :items="field.items"
                        :placeholder="`Pilih ${field.nama}`"
                      />
                    </template>
                    <template v-if="field.type == 'wilayah'">
                      <AppAutocomplete
                        :id="field.id"
                        v-model="form[field.id]"
                        :rules="[requiredValidator]"
                        :items="field.items"
                        :placeholder="`Pilih ${field.nama}`"
                        item-title="nama"
                        item-value="kode_wilayah"
                        @update:model-value="changeWilayah(field.id, $event)"
                      />
                    </template>
                  </VCol>
                </VRow>
              </VCol>
            </template>
            <VCol cols="12">
              <VRow no-gutters>
                <VCol cols="12" md="3" />
                <VCol cols="12" md="9">
                  <VBtn type="submit" class="me-4"> Simpan </VBtn>
                </VCol>
              </VRow>
            </VCol>
          </VRow>
        </template>
      </VCardText>
    </VForm>
  </VCard>
  <VSnackbar v-model="isSnackbarTopEndVisible" color="success" location="top end">
    PTK baru berhasil disimpan.
  </VSnackbar>
</template>
