<template>
    <div class="adm-detail-block" id="tabControl_layout">
        <div class="adm-detail-tabs-block" id="tabControl_tabs" style="left: 0;">

            <span
                v-for="(tab, key) in tabs"
                :title="tab.$attrs.headerTitle"
                :id="`tab_cont_${tab.id}`"
                class="adm-detail-tab"
                :class="tabClass(tab.hash)"
                @click.prevent="selectTab(tab.hash);"
            >
                {{tab.$attrs.header}}
            </span>

            <div
                class="adm-detail-title-setting"
                ref="tabControl_expand_link"
                :class="tabSelectAll?'adm-detail-title-setting-active':''"
                :title="expandLinkData.title"
                @click.prevent="tabSelectAll=!tabSelectAll"
            >
                <span class="adm-detail-title-setting-btn adm-detail-title-expand"></span>
            </div>

            <!--            <div onclick="tabControl.ToggleFix('top')" class="adm-detail-pin-btn-tabs" title="Открепить панель"></div>-->

        </div>

        <div class="adm-detail-content-wrap">
            <form
                :method="formData.method"
                :action="formData.action"
                :enctype="formData.enctype"
                :name="formData.name"
                :class="formData.class"
                :ref="formData.name"
            >
                <input type="hidden" name="sessid" id="sessid" :value="formData.sessid">

                <slot/>

<!--                <div-->
<!--                    v-for="(tab, key) in tabs"-->
<!--                    class="adm-detail-content"-->
<!--                    :id="tab.id"-->
<!--                    v-if="isActive(key)"-->
<!--                >-->
<!--                    <div class="adm-detail-title" v-if="!isEmpty(tab.detailTitle)">-->
<!--                        {{tab.detailTitle}}-->
<!--                    </div>-->
<!--                    <div class="adm-detail-content-item-block">-->
<!--                        {{tabContent(tab)}}-->
<!--                    </div>-->
<!--                </div>-->

                <div class="adm-detail-content-btns-wrap adm-detail-content-btns-fixed">
                    <div class="adm-detail-content-btns" style="display: flex;">
                        <!--                        <div onclick="tabControl.ToggleFix('bottom')" class="adm-detail-pin-btn" title="Открепить панель"></div>-->
                        <div class="btn--wrapper">
                            <div class="adm-btn-load-img" v-show="isSubmit"></div>
                            <input
                                type="submit"
                                name="save"
                                value="Сохранить"
                                title="Сохранить и вернуться"
                                class="adm-btn-save"
                                @click.prevent="$emit('save')"
                                :disabled="isSubmit"
                                :class="isSubmit?'adm-btn-load':''"
                            >
                        </div>

                        <div style="display: flex;justify-content: center; align-items: center;">
                            <div class="adm-btn-load-img" v-show="isSubmit" style="z-index: 1;"></div>
                            <input
                                type="submit"
                                name="apply"
                                value="Применить"
                                title="Сохранить и остаться в форме"
                                @click.prevent="$emit('apply')"
                                :disabled="isSubmit"
                                :class="isSubmit?'adm-btn-load':''"
                            >
                        </div>

                        <input type="button"
                               value="Отменить"
                               name="cancel"
                               onclick="top.window.location='/bitrix/admin/settings.php?lang=ru&amp;mid=dev2fun.multidomain&amp;mid_menu=1'"
                               title="Не сохранять и вернуться"
                        >
                    </div>
                </div>

            </form>
        </div>
    </div>
</template>

<script>
    export default {
        name: "bxTabs",
        props: {
            // tabs: {
            //     type: Object,
            //     require: true,
            //     default() {
            //         return {
            //             settings: {
            //                 id: 'editSettings',
            //                 title: 'Настройка параметров модуля',
            //                 label: 'Настройки',
            //                 detailTitle: 'Настройка параметров модуля',
            //                 content: '', // контент
            //                 component: '', // компонент
            //             },
            //         };
            //     },
            // },
            formSettings: {
                type: Object,
                default() {
                    return {
                        method: 'post',
                        action: '/bitrix/admin/settings.php?mid=dev2fun.multidomain&lang=ru&tabControl_active_tab=edit1',
                        enctype: 'multipart/form-data',
                        name: 'editform',
                        class: 'editform',
                        sessid: '', // required
                    };
                }
            },
            selected: {
                type: String,
                default() {
                    return ''
                },
            },
            isSubmit: {
                type: Boolean,
                default(){
                    return false;
                }
            },
        },
        data() {
            return {
                tabSelectAll: false,
                tabSelect: 'settings',
                activeTabHash: '',
                activeTabIndex: '',
                tabs: [],
                options: {
                    useUrlFragment: true,
                    defaultTabHash: null,
                },
            };
        },
        computed: {
            expandLinkData() {
                let result = {};
                if(this.tabSelectAll) {
                    result = {
                        title: 'Развернуть все вкладки на одну страницу',
                    };
                } else {
                    result = {
                        title: 'Свернуть вкладки',
                    };
                }
                this.$emit('expanded', result);
                return result;
            },
            formData() {
                console.log(Object.assign(
                    {},
                    {
                        method: 'post',
                        action: location.href,
                        enctype: 'multipart/form-data',
                        name: 'editform',
                        class: 'editform',
                        sessid: '',
                    },
                    this.formSettings
                ));
                return Object.assign(
                    {},
                    {
                        method: 'post',
                        action: location.href,
                        enctype: 'multipart/form-data',
                        name: 'editform',
                        class: 'editform',
                        sessid: '',
                    },
                    this.formSettings
                );
            },
            // isAuth() {
            //     return this.$store.getters['user/auth'];
            // },
        },
        created() {
            if(!this.isEmpty(this.selected)) {
                this.options.defaultTabHash = this.selected;
            }
            this.tabs = this.$children;
        },
        mounted() {
            window.addEventListener('hashchange', () => this.selectTab(window.location.hash));
            if (this.findTab(window.location.hash)) {
                this.selectTab(window.location.hash);
                return;
            }
            if(this.options.defaultTabHash !== null && this.findTab("#" + this.options.defaultTabHash)) {
                this.selectTab("#" + this.options.defaultTabHash);
                return;
            }
            if (this.tabs.length) {
                this.selectTab(this.tabs[0].hash);
            }
        },
        methods: {
            findTab(hash) {
                return this.tabs.find(tab => tab.hash === hash);
            },
            isActive(key) {
                return key === this.tabSelect;
            },
            tabClass(key) {
                // let className = 'c-tab-heading--' + key;
                if (this.isActive(key) && !this.tabSelectAll) {
                    return 'adm-detail-tab-active';
                    // className = 'c-tab-heading--active';
                }
                return '';
                // return className;
            },
            selectTab(key, event) {
                if(key == this.tabSelect) {
                    return;
                }
                const selectedTab = this.findTab(key);
                if (! selectedTab) {
                    return;
                }

                // if (selectedTab.isDisabled) {
                //     event.preventDefault();
                //     return;
                // }

                // if (this.lastActiveTabHash === selectedTab.hash) {
                //     this.$emit('clicked', { selectedTab });
                //     return;
                // }
                this.tabs.forEach(tab => {
                    tab.isActive = (tab.hash === selectedTab.hash);
                });
                // this.$emit('changed', { tab: selectedTab });
                this.activeTabHash = selectedTab.hash;
                this.activeTabIndex = this.getTabIndex(key);
                this.tabSelect = selectedTab.hash;
                location.hash = selectedTab.id;

                this.$emit('selected', key);
            },
            getTabIndex(hash) {
                const tab = this.findTab(hash);
                return this.tabs.indexOf(tab);
            },
            getTabHash(index){
                const tab = this.tabs.find(tab => this.tabs.indexOf(tab) === index);
                if (!tab) return;
                return tab.hash;
            },
            setShowAll(action=null) {
                this.tabSelectAll = action ?? !this.tabSelectAll;
                this.$emit('selectedAll', this.tabSelectAll);
            },
            tabContent(tab) {
                if(!this.isEmpty(tab.component)) {
                    return tab.component();
                }
                if(!this.isEmpty(tab.content)) {
                    return tab.content;
                }
                return '';
            },
        },
    }
</script>

<style scoped>
    .btn--wrapper {
        display: flex;
        justify-content: center;
        align-items: center;
    }
    .btn--wrapper .adm-btn-load-img{
        z-index: 1;
    }
</style>