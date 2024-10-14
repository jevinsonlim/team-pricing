import axios from 'axios'
import { createJsonapiStore } from 'pinia-jsonapi'

const api = axios.create({
  baseURL: '/api/v1/',
  headers: {
    'Content-Type': 'application/vnd.api+json',
    'Accept': 'application/vnd.api+json'
  }
})

const config = { preserveJson: true }

const { jsonapiStore } = createJsonapiStore(api, config)

export { jsonapiStore }