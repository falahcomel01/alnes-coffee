import { create } from 'zustand'

const useSettingStore = create((set) => ({
  setting: null,
  setSetting: (setting) => set({ setting }),
}))

export default useSettingStore