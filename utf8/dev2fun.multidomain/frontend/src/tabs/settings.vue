<template>
    <table class="adm-detail-content-table edit-table" id="edit1_edit_table">
        <tbody>
        <!--    <tr class="heading">-->
        <!--        <td colspan="2"><b>--><!--</b></td>-->
        <!--    </tr>-->

        <tr>
            <td width="40%" class="adm-detail-content-cell-l">
                <label>
                    {{locale.LABEL_ENABLE}}:
                </label>
            </td>
            <td width="60%" class="adm-detail-content-cell-r">
                <input
                    type="checkbox"
                    name="enable"
                    id="enable"
                    value="Y"
                    class="adm-designed-checkbox"
                    v-model="inputValue.enable"
                >
                <label
                    class="adm-designed-checkbox-label"
                    for="enable"
                    title=""
                ></label>
            </td>
        </tr>

        <tr>
            <td width="40%" class="adm-detail-content-cell-l">
                <label for="logic_subdomain">
                    {{locale.LABEL_ALGORITM}}:
                </label>
            </td>
            <td width="60%" class="adm-detail-content-cell-r">
                <select id="logic_subdomain" name="logic_subdomain" v-model="inputValue.logic_subdomain">
<!--                    <option value="virtual">-->
<!--                        {{locale.LABEL_VIRTUAL}}-->
<!--                    </option>-->
                    <option value="subdomain">
                        {{locale.LABEL_SUBDOMAIN}} (sub.site.ru)
                    </option>
                    <option value="directory">
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
                    <option value="lang">{{locale.LABEL_TYPE_LANG}}</option>
                    <option value="virtual">{{locale.LABEL_VIRTUAL}}</option>
                </select>
            </td>
        </tr>


<!--        <tr>-->
<!--            <td width="40%" class="adm-detail-content-cell-l"></td>-->
<!--            <td width="60%" class="adm-detail-content-cell-r">-->
<!--                <i>{{locale.DESCRIPTION_TYPE}}</i>-->
<!--            </td>-->
<!--        </tr>-->

        <tr>
            <td width="40%" class="adm-detail-content-cell-l">
                <label for="enable_replace_links">
                    {{locale.LABEL_ENABLE_REPLACE_LINKS}}:
                </label>
            </td>
            <td width="60%" class="adm-detail-content-cell-r">
                <input
                    type="checkbox"
                    id="enable_replace_links"
                    name="enable_replace_links"
                    v-model="inputValue.enable_replace_links"
                />
            </td>
        </tr>

<!--        <tr>-->
<!--            <td width="40%" class="adm-detail-content-cell-l">-->
<!--                <label for="auto_rewrite">-->
<!--                    {{locale.LABEL_ENABLE_AUTO_REWRITE}}:-->
<!--                </label>-->
<!--            </td>-->
<!--            <td width="60%" class="adm-detail-content-cell-r">-->
<!--                <input-->
<!--                    type="checkbox"-->
<!--                    id="auto_rewrite"-->
<!--                    name="auto_rewrite"-->
<!--                    v-model="inputValue.auto_rewrite"-->
<!--                />-->
<!--            </td>-->
<!--        </tr>-->

        <tr>
            <td width="40%" class="adm-detail-content-cell-l">
                <label for="key_ip">
                    {{locale.LABEL_IP}}:
                </label>
            </td>
            <td width="60%" class="adm-detail-content-cell-r">
                <select name="key_ip" v-model="inputValue.key_ip">
                    <option value="HTTP_X_REAL_IP">HTTP_X_REAL_IP (IP:{{settings.realIp}})</option>
                    <option value="REMOTE_ADDR" selected>REMOTE_ADDR (IP:{{settings.remoteAddr}})</option>
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
                                v-if="!domainKeysLocal.length"
                                type="text"
                                size="50"
                                :name="`MAPLIST[${inxd}][KEY]`"
                                :placeholder="locale.LABEL_MAPPING_LIST_KEY"
                                v-model="map.KEY"
                                style="float: left;"
                            >
                            <select
                                v-else
                                v-model="map.KEY"
                                :name="`MAPLIST[${inxd}][KEY]`"
                            >
                                <option
                                    v-for="domainKey in domainKeysLocal"
                                    :value="domainKey.id"
                                    :key="domainKey.id"
                                >
                                    {{ domainKey.title }}
                                </option>
                            </select>
                            <input
                                type="text"
                                size="50"
                                :name="`MAPLIST[${inxd}][SUBNAME]`"
                                :placeholder="locale.LABEL_MAPPING_LIST_SUBNAME"
                                v-model="map.SUBNAME"
                                style="float: left;"
                            >
                            <div
                                :style="{
                                    position: 'relative',
                                    float: 'left',
                                    float: 'left',
                                    height: '26px',
                                    width: '40px',
                                }"
                            >
                                <span
                                    class="adm-warning-close"
                                    @click="removeMapList(inxd)"
                                    :style="{
                                        'background-position-y': '-2929px',
                                        height: 'inherit',
                                    }"
                                ></span>
                            </div>
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
                                :style="{
                                    float: 'left',
                                }"
                            >
                            <div :style="{
                                position: 'relative',
                                float: 'left',
                                height: '26px',
                            }">
                                <span
                                    class="adm-warning-close"
                                    @click="removeExcludePath(indx)"
                                    :style="{
                                        'background-position-y': '-2929px',
                                        height: 'inherit',
                                    }"
                                ></span>
                            </div>
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


        <tr class="heading" v-if="isDirectoryMode">
            <td colspan="2">
                <b>{{ locale.LABEL_URLREWRITE }}</b>
            </td>
        </tr>

        <template v-if="isDirectoryMode && siteId">

            <tr>
                <td width="100%" colspan="2" class="adm-detail-content-cell-r">
                    <urlrewrite
                        v-model="inputValue"
                        :site-id="String(siteId)"
                        :locale="locale"
                        @saved="savedUrlrewrite"
                    />
                </td>
            </tr>

<!--            <tr>-->
<!--                <td width="100%" colspan="2" class="adm-detail-content-cell-r">-->
<!--                    <div style="margin: 0 auto; display: block; width: 50%;">-->
<!--                        <div>-->
<!--                            <input-->
<!--                                type="button"-->
<!--                                style="margin: 0 auto; display: block;"-->
<!--                                :value="btnUpdateUrlrewriteValue(siteId)"-->
<!--                                @click.prevent="updateUrlrewrite(siteId)"-->
<!--                            >-->
<!--                        </div>-->
<!--                        <br />-->
<!--                        <div>-->
<!--                            <input-->
<!--                                type="button"-->
<!--                                style="margin: 0 auto; display: block;"-->
<!--                                :value="btnRestoreUrlrewriteValue(siteId)"-->
<!--                                @click.prevent="restoreUrlrewrite(siteId)"-->
<!--                            >-->
<!--                        </div>-->
<!--                        &lt;!&ndash;                    <div class="adm-info-message">&ndash;&gt;-->
<!--                        &lt;!&ndash;                        <i>{{ locale.LABEL_URLREWRITE_INFO1 }}</i>&ndash;&gt;-->
<!--                        &lt;!&ndash;                        <br>&ndash;&gt;-->
<!--                        &lt;!&ndash;                        <b><i>{{ locale.LABEL_URLREWRITE_INFO2 }}</i></b>&ndash;&gt;-->
<!--                        &lt;!&ndash;                    </div>&ndash;&gt;-->
<!--                    </div>-->
<!--                </td>-->
<!--            </tr>-->

<!--            v-if="isDirectoryMode"-->
            <tr>
                <td width="100%" colspan="2" class="adm-detail-content-cell-r">
                    <div style="margin: 0 auto; display: block; width: 50%;">
                        <div class="adm-info-message">
                            <i>{{ locale.LABEL_URLREWRITE_INFO1 }}</i>
                            <br>
                            <b><i>{{ locale.LABEL_URLREWRITE_INFO2 }}</i></b>
                        </div>
                    </div>
                </td>
            </tr>
        </template>

        </tbody>
    </table>
</template>

<script>
    import http from "@/methods/http";

    export default {
        name: "settings",
        components: {
            urlrewrite: () => import('../components/urlrewrite'),
        },
        props: {
            // remoteAddr: String,
            // realIp: String,
            value: Object,
            settings: Object,
            locale: Object,
            siteId: {
                type: String,
                require: true,
            },
        },
        data(){
            return {
                domainKeysLocal: [],
            };
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
            isDirectoryMode() {
                return this.inputValue.logic_subdomain==='directory';
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
            removeExcludePath(indx) {
                this.inputValue.EXCLUDE_PATH.splice(indx, 1);
                this.$forceUpdate();
            },
            removeMapList(indx) {
                this.inputValue.MAPLIST.splice(indx, 1);
                this.$forceUpdate();
            },
            savedUrlrewrite(success, text) {
                if (success) {
                    this.$emit('showMessage', text);
                } else {
                    this.$emit('error', text);
                }
            },
            async getDomainKeys() {
                if(!this.isEmpty(this.iblocksLocal)) {
                    return this.iblocksLocal;
                }
                try {
                    let settings = this.settings;
                    if (this.isEmpty(settings.url)) {
                        settings.url = location.href
                    }
                    let response = await http.post(settings.url,{
                        action: 'getDomainKeys',
                        sessid: BX.bitrix_sessid(),
                        typeSubdomain: this.inputValue.type_subdomain,
                    });
                    if (!response.success) {
                        throw new Error(response.msg.exception ?? response.msg);
                    }
                    this.domainKeysLocal = response.data;
                } catch (e) {
                    this.$emit('error', e.message);
                }
                return this.iblocksLocal;
            },
        },
    }
</script>

<style scoped>

</style>