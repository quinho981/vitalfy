// const userStore = useUserStore();

export const useHelpers = () => {
    function formatPtBrCurto(iso) {
        const d = new Date(iso);
        const day = String(d.getUTCDate()).padStart(2, '0');
        let month = new Intl.DateTimeFormat('pt-BR', { month: 'short', timeZone: 'UTC' }).format(d);
        month = month.replace('.', '');
        month = month.charAt(0).toUpperCase() + month.slice(1);
        const year = d.getUTCFullYear();
        return `${day} de ${month}, ${year}`;
    }

    const convertSecondsToMinutes = (seconds) => {
        const hours = Math.floor(seconds / 3600);
        const mins = Math.floor((seconds % 3600) / 60);
        const secs = Math.floor(seconds % 60);

        const parts = [];

        if (hours > 0) parts.push(`${hours}h`);
        if (mins > 0) parts.push(`${mins}min`);
        if (secs > 0) parts.push(`${secs}s`);

        return parts.length ? parts.join(' ') : '0s';
    };

    const capitalizeFirstLetter = (text) => {
        if (!text) return '';
        return text.charAt(0).toUpperCase() + text.slice(1);
    };
    const capitalizeArray = (arr) => {
        if (!arr) return;
        return arr.map(item => capitalizeFirstLetter(item));
    }

    const formatDate = (date) => {
        if (!date) return '';
        return new Date(date).toLocaleDateString('pt-BR');
    };

    const formatSize = (size) => {
        if (!size) return '';
        if (size < 1024) return `${size} B`;
        if (size < 1024 * 1024) return `${(size / 1024).toFixed(1)} KB`;
        return `${(size / (1024 * 1024)).toFixed(2)} MB`;
    };

    const formatTime = (seconds) => {
        const m = String(Math.floor(seconds / 60)).padStart(2, '0');
        const s = String(seconds % 60).padStart(2, '0');
        return `${m}:${s}`;
    };

    const AVATAR_PALETTES = [
        'bg-blue-100 text-blue-800 dark:bg-blue-700/50 dark:text-blue-200',
        'bg-teal-100 text-teal-800 dark:bg-teal-700/50 dark:text-teal-200',
        'bg-purple-100 text-purple-800 dark:bg-purple-700/50 dark:text-purple-200',
        'bg-amber-100 text-amber-800 dark:bg-amber-700/50 dark:text-amber-200',
        'bg-pink-100 text-pink-800 dark:bg-pink-700/50 dark:text-pink-200',
        'bg-green-100 text-green-800 dark:bg-green-700/50 dark:text-green-200',
        'bg-orange-100 text-orange-800 dark:bg-orange-700/50 dark:text-orange-200',
        'bg-slate-100 text-slate-700 dark:bg-slate-700/50 dark:text-slate-200',
    ]

    const hashName = (name = '') => {
        let h = 0
        for (let i = 0; i < name.length; i++) h = (h * 31 + name.charCodeAt(i)) & 0xffff
        return h
    }

    const getInitials = (name = '') =>
        name.split(/\s+/).map(w => w[0]).join('').slice(0, 2).toUpperCase()

    const getPatientAvatar = (name = '') =>
        AVATAR_PALETTES[hashName(name) % AVATAR_PALETTES.length]

    const getNextMonthResetDate = () => {
        const now = new Date();

        const nextMonthDate = new Date(
            now.getFullYear(),
            now.getMonth() + 1,
            1
        );

        return nextMonthDate.toLocaleDateString('pt-BR', {
            day: '2-digit',
            month: '2-digit'
        });
    };

    return {
        formatPtBrCurto,
        convertSecondsToMinutes,
        capitalizeArray,
        formatDate,
        formatSize,
        formatTime,
        getInitials,
        getPatientAvatar,
        getNextMonthResetDate
    };
}