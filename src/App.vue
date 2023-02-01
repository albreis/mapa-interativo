<template>
  <div id="app">
    <div class="mapa">
      <img class="mapa-img" src="@/assets/mapa.svg" alt="" @dblclick.stop="add_marker($event)">
      <template v-for="(marker, key) in markers">
        <span :key="key" :style="marker.style" class="marker">
          <select v-if="marker.status == 'ativo' && is_admin" @change="change(marker)" class="title" v-model="marker.title">
            <template v-for="city in cities">
              <option :key="city" :value="city" v-html="city.name"></option>
            </template>
          </select>
          <span v-if="marker.status == 'hover' || (marker.status == 'ativo' && !is_admin)" class="title" v-html="marker.title.name"></span>
          <img @mouseenter="enter(marker)" @mouseleave="inativo(marker)" @click="activate(marker)" :src="require(`@/assets/${marker.status}.svg`)" alt="">
          <span v-if="is_admin" class="close" @click="remove(key)">x</span>
        </span>
      </template>
    </div>
  </div>
</template>

<script>

export default {
  name: 'App',
  components: {
  },
  data() {
    return {
      tmp_title: '',
      markers: [],
      cities: [],
      is_admin: false
    }
  },
  methods: {
    add_marker(e) {
      if(!this.is_admin) return;
      this.markers.push({ 
        status: 'inativo', 
        title: {term_id: 0, name: `Marker ${this.markers.length}`}, 
        style: {
          left: `calc(${((e.layerX / e.target.width) * 100)}% - 12px)`, 
          top: `calc(${((e.layerY / e.target.height) * 100)}% - 35px)`
        }
      });
      this.save()
    },
    title(marker, e) {
      marker.title = e.target.innerHTML
    },
    change(marker) {
      if(!this.is_admin) return;
      this.filter_items(marker)
      this.save()
    },
    activate(marker) {
      if(marker.status == 'ativo') {
        marker.status = 'hover';
        return;
      }
      this.tmp_title = marker.title.name
      this.markers.forEach(item => item.status = 'inativo')
      marker.status = 'ativo'
      this.filter_items(marker)
    },
    inativo(marker) {
      if(marker.status == 'ativo') return;
      marker.status = 'inativo'
    },  
    enter(marker) {
      if(marker.status == 'ativo') return;
      marker.status = 'hover'
    },  
    remove(index) {
      this.markers.splice(index, 1)
      this.save()
    },
    filter_items(marker) {
      if(this.bus) this.bus.$emit('select_cidade', marker.title.term_id)
    },
    save() {
      if(!this.is_admin) return;
      this.markers.forEach(item => item.status = 'inativo')
      this.axios.post(`/wp-json/mapa-interativo/v1/markers`, this.markers)
    }
  },
  computed: {
    bus() {
      return window.$bus
    }
  },
  mounted() {
    this.axios.get(`/wp-json/mapa-interativo/v1/is_admin`).then(res => this.is_admin = res.data)
    this.axios.get(`/wp-json/mapa-interativo/v1/markers?v${(new Date).getTime()}`).then(res => {
      if(res.data) this.markers = res.data
    })
    this.axios.get(`/wp-json/mapa-interativo/v1/unidades`).then(res => {
      if(res.data) this.cities = res.data
    })
  },
  watch: {
    markers: {
      deep: true,
      handler() {
        localStorage.markers = JSON.stringify(this.markers)
      }
    }
  }
}
</script>

<style lang="stylus" scoped>
#app {
  font-family: sans-serif;
  img.mapa-img {
    width: 100%;
    display: block;
    margin: auto;
  }
  .mapa {
    position: relative;
    display: block;
    margin: auto;
  }
  .marker {
    position: absolute;
    left: 0;
    top: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    font-size: 13px;
    .title {
      position: absolute;
      bottom: 110%;
      white-space: nowrap;
      padding: 10px;
      background: #fff;
      border-radius: 5px;
      border: none;
      &:focus {
        outline: none;
      }
      &select {
        width: 200px;
      }
    }
    img {
      width: 24px;
      max-width: 24px;
      cursor: pointer;
    }  
    .close {
      position: absolute;
      top: 10px;
      right: -10px;
      cursor: pointer;
    }
  }  
}
</style>
