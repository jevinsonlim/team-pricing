import axios from 'axios'
import { createJsonapiStore } from 'pinia-jsonapi'

const api = axios.create({
  baseURL: '/api/v1/',
  headers: {
    'Content-Type': 'application/vnd.api+json'
  },
  withXSRFToken: true
})

const { jsonapiStore } = createJsonapiStore(api)

export { jsonapiStore }