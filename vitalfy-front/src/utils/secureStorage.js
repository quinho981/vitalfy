import Cookies from 'js-cookie';

// Deriva uma chave HMAC a partir do client_nonce — cookie não-HttpOnly gerado
// pelo servidor a cada login. Muda a cada sessão, impossibilitando re-uso de assinaturas.
async function deriveKey() {
    const nonce = Cookies.get('client_nonce');
    if (!nonce) return null;

    const encoder = new TextEncoder();
    return crypto.subtle.importKey(
        'raw',
        encoder.encode(nonce),
        { name: 'HMAC', hash: 'SHA-256' },
        false,
        ['sign', 'verify']
    );
}

async function sign(data) {
    const key = await deriveKey();
    if (!key) return null;

    const encoder = new TextEncoder();
    const sig = await crypto.subtle.sign('HMAC', key, encoder.encode(JSON.stringify(data)));
    return btoa(String.fromCharCode(...new Uint8Array(sig)));
}

async function verify(data, signature) {
    try {
        const key = await deriveKey();
        if (!key) return false;

        const encoder = new TextEncoder();
        const sigBuffer = Uint8Array.from(atob(signature), c => c.charCodeAt(0));
        return crypto.subtle.verify('HMAC', key, sigBuffer, encoder.encode(JSON.stringify(data)));
    } catch {
        return false;
    }
}

export const secureStorage = {
    async set(key, value, storage = 'local') {
        const sig = await sign(value);
        if (!sig) return;

        const payload = JSON.stringify({ v: value, s: sig });
        if (storage === 'local') {
            localStorage.setItem(key, payload);
        } else {
            Cookies.set(key, payload);
        }
    },

    // Retorna o valor ou null se ausente/adulterado.
    // null indica ao chamador que deve re-buscar do servidor.
    async get(key, storage = 'local') {
        const raw = storage === 'local'
            ? localStorage.getItem(key)
            : Cookies.get(key);

        if (!raw) return null;

        try {
            const { v, s } = JSON.parse(raw);
            const valid = await verify(v, s);
            if (!valid) {
                if (storage === 'local') localStorage.removeItem(key);
                else Cookies.remove(key);
                return null;
            }
            return v;
        } catch {
            if (storage === 'local') localStorage.removeItem(key);
            else Cookies.remove(key);
            return null;
        }
    },

    remove(key, storage = 'local') {
        if (storage === 'local') localStorage.removeItem(key);
        else Cookies.remove(key);
    },
};
