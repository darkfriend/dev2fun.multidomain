<template>
    <table class="adm-detail-content-table edit-table" id="edit1_edit_table">
        <tbody>
        <!--    <tr class="heading">-->
        <!--        <td colspan="2"><b>--><!--</b></td>-->
        <!--    </tr>-->
        <tr>
            <td width="40%" class="adm-detail-content-cell-l">
                <label for="logic_subdomain">
                    {{locale.LABEL_ALGORITM}}:
                </label>
            </td>
            <td width="60%" class="adm-detail-content-cell-r">
                <select id="logic_subdomain" name="logic_subdomain" v-model="inputValue.logic_subdomain">
                    <option value="virtual">
                        {{locale.LABEL_VIRTUAL}}
                    </option>
                    <option value="subdomain">
                        {{locale.LABEL_SUBDOMAIN}} (sub.site.ru)
                    </option>
                    <option value="directory" disabled>
                        {{locale.LABEL_DIRECTORY}} (site.ru/sub/)
                    </option>
                </select>
            </td>
        </tr>

        <tr>
            <td width="40%" class="adm-detail-content-cell-l">
                <label for="type_subdomain">
                    {{locale.LABEL_TYPE}}:
                </label>
            </td>
            <td width="60%" class="adm-detail-content-cell-r">
                <select name="type_subdomain" v-model="inputValue.type_subdomain">
                    <option value="city">{{locale.LABEL_CITY}}</option>
                    <option value="country">{{locale.LABEL_COUNTRY}}</option>
                </select>
            </td>
        </tr>

        <tr>
            <td width="40%" class="adm-detail-content-cell-l"></td>
            <td width="60%" class="adm-detail-content-cell-r">
                <i>{{locale.DESCRIPTION_TYPE}}</i>
            </td>
        </tr>

        <tr>
            <td width="40%" class="adm-detail-content-cell-l">
                <label for="key_ip">
                    {{locale.LABEL_IP}}:
                </label>
            </td>
            <td width="60%" class="adm-detail-content-cell-r">
                <select name="key_ip" v-model="inputValue.key_ip">
                    <option value="HTTP_X_REAL_IP">HTTP_X_REAL_IP (IP:{{settings.realIp}})</option>
                    <option value="REMOTE_ADDR" selected="">REMOTE_ADDR (IP:{{settings.remoteAddr}})</option>
                </select>
            </td>
        </tr>

        <tr>
            <td width="40%" class="adm-detail-content-cell-l">
                <label for="lang_default">
                    {{locale.LABEL_DOMAIN_DEFAULT}}:
                </label>
            </td>
            <td width="60%" class="adm-detail-content-cell-r">
                <input type="text" name="domain_default" v-model="inputValue.domain_default">
                <br>
                <i>{{locale.DESCRIPTION_DOMAIN_DEFAULT}}</i>
            </td>
        </tr>

        <tr>
            <td width="40%" class="adm-detail-content-cell-l">
                <label>
                    {{locale.LABEL_MAPPING_LIST}}:
                </label>
            </td>
            <td width="60%" class="adm-detail-content-cell-r">
                <table class="nopadding" cellpadding="0" cellspacing="0" border="0" width="100%"
                       id="d2f_mapping_list">
                    <tbody>
                    <tr v-for="(map, inxd) in inputValue.MAPLIST">
                        <td>
                            <input
                                type="text"
                                size="50"
                                :name="`MAPLIST[${inxd}][KEY]`"
                                :placeholder="locale.LABEL_MAPPING_LIST_KEY"
                                v-model="map.KEY"
                            >
                            <input
                                type="text"
                                size="50" :name="`MAPLIST[${inxd}][SUBNAME]`"
                                :placeholder="locale.LABEL_MAPPING_LIST_SUBNAME"
                                v-model="map.SUBNAME"
                            >
                        </td>
                    </tr>
<!--                    <tr v-for="map in input.MAPLIST">-->
<!--                        <td>-->
<!--                            <input-->
<!--                                type="text"-->
<!--                                size="50"-->
<!--                                name="MAPLIST[n0][KEY]"-->
<!--                                value=""-->
<!--                                placeholder="Ключ"-->
<!--                                v-model="map.KEY"-->
<!--                            >-->
<!--                            <input-->
<!--                                type="text"-->
<!--                                size="50"-->
<!--                                name="MAPLIST[n0][SUBNAME]"-->
<!--                                value=""-->
<!--                                placeholder="Поддомен"-->
<!--                                v-model="map.SUBNAME"-->
<!--                            >-->
<!--                        </td>-->
<!--                    </tr>-->
                    <tr>
                        <td>
                            <input
                                type="button"
                                :value="locale.LABEL_ADD"
                                @click.prevent="addNewRow()"
                            >
                        </td>
                    </tr>
                    </tbody>
                </table>
            </td>
        </tr>


        <tr>
            <td width="40%" class="adm-detail-content-cell-l">
                <label for="path_to_optipng">
                    {{locale.LABEL_EXCLUDE_PATH}}:
                </label>
            </td>
            <td width="60%" class="adm-detail-content-cell-r">
                <table class="nopadding" cellpadding="0" cellspacing="0" border="0" width="100%" id="d2f_exclude_path">
                    <tbody>
<!--                    <tr>-->
<!--                        <td>-->
<!--                            <input-->
<!--                                type="text"-->
<!--                                size="80" name="EXCLUDE_PATH[n0]"-->
<!--                                value="\/(bitrix|local)\/(admin|tools)\/"-->
<!--                                placeholder="Регулярное выражение"-->
<!--                            >-->
<!--                        </td>-->
<!--                    </tr>-->
                    <tr v-for="(exPath,indx) in inputValue.EXCLUDE_PATH">
                        <td>
                            <input
                                type="text"
                                size="80"
                                v-model="inputValue.EXCLUDE_PATH[indx]"
                                :placeholder="locale.LABEL_EXCLUDE_PATH_REG"
                            >
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <input
                                type="button"
                                :value="locale.LABEL_ADD"
                                @click.prevent="addNewRowExclude()"
                            >
                        </td>
                    </tr>
<!--                    <script type="application/javascript">-->
<!--                        BX.addCustomEvent('onAutoSaveRestore', function (ob, data) {-->
<!--                            for (var i in data) {-->
<!--                                if (i.substring(0, 9) == 'EXCLUDE_PATH[') {-->
<!--                                    addNewRow('d2f_exclude_path')-->
<!--                                }-->
<!--                            }-->
<!--                        });-->
<!--                    </script>-->
                    </tbody>
                </table>
            </td>
        </tr>


        </tbody>
    </table>
</template>

<script>
    export default {
        name: "settings",
        props: {
            // remoteAddr: String,
            // realIp: String,
            value: Object,
            settings: Object,
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
        methods: {
            addNewRow() {
                if(typeof this.inputValue.MAPLIST != 'object') {
                    this.inputValue.MAPLIST = [];
                }
                this.inputValue.MAPLIST.push({
                    KEY: '',
                    SUBNAME: '',
                });
                this.$forceUpdate();
            },
            addNewRowExclude() {
                if(typeof this.inputValue.EXCLUDE_PATH != 'object') {
                    this.inputValue.EXCLUDE_PATH = [];
                }
                this.inputValue.EXCLUDE_PATH.push('');
                this.$forceUpdate();
            },
        },
    }
</script>

<style scoped>

</style>