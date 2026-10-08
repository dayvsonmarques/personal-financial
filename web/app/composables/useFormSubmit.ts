import type { FormError } from '@nuxt/ui'

interface FormHandle {
  setErrors: (errors: FormError[]) => void
  clear: (name?: string) => void
}

/**
 * Executa o envio de um UForm: erros 422 vão para os campos; os demais viram
 * uma mensagem geral (`error`) para exibir num UAlert.
 *
 * O erro do servidor some assim que o campo é editado. Sem isso ele só sumiria
 * no blur, e o deslocamento do layout no clique em "Salvar" faz o clique se perder.
 */
export function useFormSubmit(form: Readonly<Ref<FormHandle | null | undefined>>, state: object) {
  const values = state as Record<string, unknown>
  const error = ref<string | null>(null)
  let stopWatching: (() => void) | undefined
  // O watch é criado depois do setup (no catch), então não é parado automaticamente.
  onScopeDispose(() => stopWatching?.())

  function clearOnEdit(fieldErrors: FormError[]) {
    stopWatching?.()
    const fields = new Map(fieldErrors.flatMap(e => (e.name ? [[e.name, values[e.name]]] : [])))
    stopWatching = watch(() => [...fields.keys()].map(name => values[name]), (current) => {
      ;[...fields.keys()].forEach((name, i) => {
        if (current[i] !== fields.get(name)) {
          form.value?.clear(name)
          fields.delete(name)
        }
      })
      if (!fields.size) stopWatching?.()
    })
  }

  async function run(action: () => Promise<unknown>): Promise<boolean> {
    error.value = null
    try {
      await action()
      return true
    }
    catch (e) {
      const fieldErrors = toFormErrors(e)
      if (fieldErrors.length) {
        form.value?.setErrors(fieldErrors)
        clearOnEdit(fieldErrors)
      }
      else {
        error.value = errorMessage(e)
      }
      return false
    }
  }

  return { error, run }
}
