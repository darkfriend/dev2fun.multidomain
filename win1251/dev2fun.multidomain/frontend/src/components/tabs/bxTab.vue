<template>
    <div
        class="adm-detail-content"
        :id="id"
        v-if="isActive"
    >
        <div class="adm-detail-title" v-if="!isEmpty(title)">
            {{title}}
        </div>
        <div class="adm-detail-content-item-block">
            <slot/>
        </div>
    </div>
</template>

<script>
    export default {
        name: "bxTab",
        props: {
            id: {
                type: String,
                default: null
            },
            name: {
                type: String,
                required: true
            },
            // key: {
            //     type: String,
            //     // required: true
            // },
            title: {
                type: String
            },
        },
        data: () => ({
            isActive: false,
            isVisible: true,
        }),
        computed: {
            header() {
                return this.name;
            },
            computedId() {
                return this.id ? this.id : this.name.toLowerCase().replace(/ /g, '-');
            },
            hash() {
                if (this.isDisabled) {
                    return '#';
                }
                return '#' + this.computedId;
            },
            // isActive(key) {
            //     return key === this.tabSelect;
            // },
        },
    }
</script>

<style scoped>

</style>