<script setup>
import authV1BottomShape from "@images/svg/auth-v1-bottom-shape.svg?raw";
import authV1TopShape from "@images/svg/auth-v1-top-shape.svg?raw";
import { VNodeRenderer } from "@layouts/components/VNodeRenderer";
import { themeConfig } from "@themeConfig";
import { Google } from "universal-social-auth";

definePage({
  meta: {
    layout: "blank",
    unauthenticatedOnly: true,
  },
});
const useAuthProvider = async (provider) => {
  await Oauth.authenticate(provider, Google).then((response) => {
    useSocialLogin(response.code, provider);
  });
};
const router = useRouter();
const ability = useAbility();
const textError = ref();
const loading = ref(false);
const useSocialLogin = async (code, provider) => {
  loading.value = true;
  const pdata = { code: code, otp: "", hash: "" };
  const res = await $api(`/auth/login/${provider}`, {
    method: "POST",
    body: pdata,
  });
  const { userAbility, accessToken, userData, error, message } = res;
  if (error) {
    textError.value = message;
  } else {
    useCookie("userAbilityRules").value = userAbility;
    ability.update(userAbility);
    useCookie("userData").value = userData;
    useCookie("accessToken").value = accessToken;
    await nextTick(() => {
      router.replace("/");
    });
  }
};
</script>

<template>
  <div class="auth-wrapper d-flex align-center justify-center pa-4">
    <div class="position-relative my-sm-16">
      <!-- 👉 Top shape -->
      <VNodeRenderer
        :nodes="h('div', { innerHTML: authV1TopShape })"
        class="text-primary auth-v1-top-shape d-none d-sm-block"
      />

      <!-- 👉 Bottom shape -->
      <VNodeRenderer
        :nodes="h('div', { innerHTML: authV1BottomShape })"
        class="text-primary auth-v1-bottom-shape d-none d-sm-block"
      />

      <!-- 👉 Auth Card -->
      <VCard
        class="auth-card"
        max-width="460"
        :class="$vuetify.display.smAndUp ? 'pa-6' : 'pa-0'"
      >
        <VCardItem class="justify-center">
          <VCardTitle>
            <RouterLink to="/">
              <div class="app-logo">
                <VNodeRenderer :nodes="themeConfig.app.logo" />
                <h1 class="app-logo-title">
                  {{ themeConfig.app.title }}
                </h1>
              </div>
            </RouterLink>
          </VCardTitle>
        </VCardItem>

        <VCardText>
          <VBtn
            block
            @click="useAuthProvider('google', Google)"
            :disable="loading"
            :loading="loading"
          >
            LOGIN DENGAN AKUN BELAJAR.ID
          </VBtn>
        </VCardText>
        <VCardText v-if="textError">
          <VAlert color="error"> {{ textError }} </VAlert>
        </VCardText>
      </VCard>
    </div>
  </div>
</template>

<style lang="scss">
@use "@core-scss/template/pages/page-auth";
</style>
