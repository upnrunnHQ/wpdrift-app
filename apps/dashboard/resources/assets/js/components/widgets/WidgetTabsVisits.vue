<template lang="html">
    <div class="card widget card-tabs">
        <div class="card-header">
            <ul class="nav nav-pills">
                <li class="nav-item">
                    <a class="nav-link active" data-toggle="tab" href="#browsers">Browsers</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#systems">Systems</a>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content">
                <div class="tab-pane fade show active" id="browsers">
                    <chart-browsers></chart-browsers>
                </div>
                <div class="tab-pane fade" id="systems">
                    <chart-systems></chart-systems>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import ChartBrowsers from './../charts/ChartBrowsers';
import ChartSystems from './../charts/ChartSystems';

export default {
    components: {
        ChartBrowsers,
        ChartSystems,
    },
};
</script>

<style lang="css">
</style>
