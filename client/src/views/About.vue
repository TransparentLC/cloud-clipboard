<template>
    <v-container fill-height>
        <v-row justify="center" align="center" class="pa-3">
            <div class="text-center">
                <div class="headline my-4">Cloud Clipboard {{$root.config.version}}</div>
                {{ $t('aboutDescription1') }}
                <br>
                {{ $t('aboutDescription2') }}
                <br>
                <a href="https://github.com/TransparentLC/cloud-clipboard" target="_blank">GitHub</a>
            </div>
        </v-row>
    </v-container>
</template>

<script>
export default {

}
</script>