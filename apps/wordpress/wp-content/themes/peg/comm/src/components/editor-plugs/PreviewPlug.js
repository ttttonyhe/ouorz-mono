import $ from 'jquery'
import './PreviewPlug.scss'

export default class PreviewPlug {
  constructor (editor) {
    this.editor = editor
    this.artalk = editor.artalk

    this.initElem()
  }

  initElem () {
    this.elem = $('<div class="artalk-editor-plug-preview"></div>')
    this.binded = false
  }

  getName () {
    return 'preview'
  }

  getBtnHtml () {
    return 'MD 预览'
  }

  getElem () {
    return this.elem
  }

  onShow () {
    this.elem.html(this.editor.getContentMarked())
    if (!this.binded) {
      $(this.editor.textareaEl).bind('input propertychange', (evt) => {
        if (this.elem.css('display') !== 'none') {
          this.elem.html(this.editor.getContentMarked())
        }
      })
      this.binded = true
    }
  }

  onHide () {}
}
