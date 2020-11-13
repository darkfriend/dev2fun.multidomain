<template>
    <table class="adm-detail-content-table edit-table" id="edit4_edit_table">
        <tbody>
        <tr>
            <td width="40%" class="adm-detail-content-cell-l">
                <label>
                    {{locale.LABEL_ENABLE_SEO_PAGE}}:
                </label>
            </td>
            <td width="60%" class="adm-detail-content-cell-r">
                <input
                    type="checkbox"
                    name="enable_seo_page"
                    id="enable_seo_page"
                    value="Y"
                    class="adm-designed-checkbox"
                    v-model="inputValue.enable_seo_page"
                >
                <label
                    class="adm-designed-checkbox-label"
                    for="enable_seo_page"
                    title=""
                ></label>
            </td>
        </tr>
        <tr>
            <td width="40%" class="adm-detail-content-cell-l">
                <label>
                    {{locale.LABEL_ENABLE_SEO_TITLE_ADD_CITY}}:
                </label>
            </td>
            <td width="60%" class="adm-detail-content-cell-r">
                <input
                    type="checkbox"
                    name="enable_seo_title_add_city"
                    value="Y"
                    id="enable_seo_title_add_city"
                    class="adm-designed-checkbox"
                    v-model="inputValue.enable_seo_title_add_city"
                >
                <label
                    class="adm-designed-checkbox-label"
                    for="enable_seo_title_add_city"
                    title=""
                ></label>
            </td>
        </tr>
        <tr>
            <td width="40%" class="adm-detail-content-cell-l">
                <label for="pattern_seo_title_add_city">
                    {{locale.LABEL_PATTERN_SEO_TITLE_ADD_CITY}}:
                </label>
            </td>
            <td width="60%" class="adm-detail-content-cell-r">
                <input
                    type="text"
                    name="pattern_seo_title_add_city"
                    v-model="inputValue.pattern_seo_title_add_city"
                >
            </td>
        </tr>

        </tbody>
    </table>
</template>

<script>
    export default {
        name: "seo",
        props: {
            settings: Object,
            value: Object,
            locale: Object,
        },
        data(){
            return {};
        },
        computed: {
            inputValue: {
                get() {
                    return this.value;
                },
                set(val) {
                    this.$emit('input', val);
                }
            },
        },
    }
</script>

<style scoped>

</style>