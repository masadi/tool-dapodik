import axios from 'axios';
import UniversalSocialauth from 'universal-social-auth';

const options = {
    providers: {
        google: {
            clientId: import.meta.env.VITE_GOOGLE_CLIENT_ID,
            redirectUri: import.meta.env.VITE_GOOGLE_CLIENT_REDIRECT
        },
    }
}
console.log(options);

export const Oauth = new UniversalSocialauth(axios, options)
