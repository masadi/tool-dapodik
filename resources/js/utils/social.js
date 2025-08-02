import { themeConfig } from "@themeConfig";
import axios from 'axios';
import UniversalSocialauth from 'universal-social-auth';
const options = {
    providers: {
        google: {
            clientId: import.meta.env.VITE_GOOGLE_CLIENT_ID,
            //redirectUri: import.meta.env.VITE_GOOGLE_CLIENT_REDIRECT,
            redirectUri: `${themeConfig.app.url}/auth/social/google/callback`
        },
    }
}
console.log(themeConfig.app.url);
console.log(options);
export const Oauth = new UniversalSocialauth(axios, options)
