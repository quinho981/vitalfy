<template>
    <div class="md:flex hidden md:w-1/2 h-screen relative items-center justify-center overflow-hidden
                bg-gradient-to-br from-blue-500 via-blue-600 to-blue-900 text-white px-12"
    >
        <div class="absolute inset-0">
            <div class="absolute bottom-0 w-full overflow-hidden leading-[0]">
                <svg class="relative block w-full h-[17rem]" preserveAspectRatio="none" viewBox="0 0 1440 320" xmlns="http://www.w3.org/2000/svg">
                    <path fill="#0a192f" fill-opacity="0.3" d="M0,128L60,144C120,160,240,192,360,186.7C480,181,600,139,720,117.3C840,96,960,96,1080,122.7C1200,149,1320,203,1380,229.3L1440,256V320H1380C1320,320,1200,320,1080,320C960,320,840,320,720,320C600,320,480,320,360,320C240,320,120,320,60,320H0V128Z"></path>
                    <path fill="#0a192f" fill-opacity="0.3" d="M0,224L60,213.3C120,203,240,181,360,186.7C480,192,600,224,720,224C840,224,960,192,1080,181.3C1200,171,1320,181,1380,186.7L1440,192V320H1380C1320,320,1200,320,1080,320C960,320,840,320,720,320C600,320,480,320,360,320C240,320,120,320,60,320H0V224Z"></path>
                </svg>
            </div>
        </div>

        <div class="relative z-10 max-w-xl text-center mb-24">
            <h1 class="text-4xl font-bold leading-tight mb-6 text-white/90">
                Transforme consultas em documentos clínicos com IA
            </h1>

            <p class="text-lg text-white/90 mb-10">
                Transcreva, organize e gere prontuários automaticamente — com precisão e rapidez.
            </p>

            <div class="w-full max-w-md mx-auto drop-shadow-2xl brightness-95">
                <MedicineIlustration />
            </div>
        </div>
    </div>
</template>

<style scoped>
</style>