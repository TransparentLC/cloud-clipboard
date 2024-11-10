<template>
    <v-container>
        <v-responsive max-width="640" class="mx-auto">
            <div class="headline text--primary my-4">{{ $t('connectedDeviceList') }}</div>
            <template v-if="$root.websocket">
                {{ $t('connectedDevicesCount', { count: $root.device.length }) }}，{{ $t('desktopDevicesCount', { count: desktopDeviceCount }) }}，{{ $t('mobileDevicesCount', { count: mobileDeviceCount }) }}。
                <v-divider class="my-2"></v-divider>
            </template>
            <template v-else>
                {{ $t('notConnectedToServer') }}。
            </template>

            <v-list
                rounded
                two-line
            >
                <v-list-item-group color="primary">
                    <v-list-item
                        v-for="item in $root.device"
                        :key="item.id"
                    >
                        <v-list-item-avatar tile>
                            <template v-if="item.type === 'desktop'">
                                <v-icon v-if="item.os.split(' ').shift() === 'Windows'">{{mdiMicrosoftWindows}}</v-icon>
                                <v-icon v-else-if="item.os.split(' ').shift() === 'GNU/Linux'">{{mdiLinux}}</v-icon>
                                <v-icon v-else-if="item.os.split(' ').shift() === 'Mac'">{{mdiApple}}</v-icon>
                                <v-icon v-else>{{mdiLaptop}}</v-icon>
                            </template>
                            <template v-else-if="item.type === 'smartphone' || item.type === 'mobile' || item.type === 'tablet'">
                                <v-icon v-if="item.os.split(' ').shift() === 'Android'">{{mdiAndroid}}</v-icon>
                                <v-icon v-else-if="item.os.split(' ').shift() === 'iOS'">{{mdiAppleIos}}</v-icon>
                                <v-icon v-else>{{mdiTabletCellphone}}</v-icon>
                            </template>
                            <v-icon v-else>{{mdiDevices}}</v-icon>
                        </v-list-item-avatar>
                        <v-list-item-content>
                            <v-list-item-title>{{
                                item.type === 'desktop' ? $t('desktopDevice') : (
                                    item.device || (
                                        (item.type === 'smartphone' || item.type === 'mobile' || item.type === 'tablet') ? $t('mobileDevice') : $t('otherTypeDevice')
                                    )
                                )
                            }}</v-list-item-title>
                            <v-list-item-subtitle>{{item.os}} ({{item.browser}})</v-list-item-subtitle>
                        </v-list-item-content>
                    </v-list-item>
                </v-list-item-group>
            </v-list>
        </v-responsive>
    </v-container>
</template>

<script>
import {
    mdiLaptop,
    mdiMicrosoftWindows,
    mdiApple,
    mdiLinux,
    mdiTabletCellphone,
    mdiAndroid,
    mdiAppleIos,
    mdiDevices,
} from '@mdi/js';

export default {
    data() {
        return {
            mdiLaptop,
            mdiMicrosoftWindows,
            mdiApple,
            mdiLinux,
            mdiTabletCellphone,
            mdiAndroid,
            mdiAppleIos,
            mdiDevices,
        };
    },
    computed: {
        desktopDeviceCount() {
            return this.$root.device.filter(e => e.type === 'desktop').length;
        },
        mobileDeviceCount() {
            return this.$root.device.filter(e => (e.type === 'smartphone' || e.type === 'tablet')).length;
        },
    },
}
</script>