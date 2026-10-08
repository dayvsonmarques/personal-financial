import type { FormError } from '@nuxt/ui'

/**
 * Executa o envio de um UForm: erros 422 vão para os campos; os demais viram
 * uma mensagem geral (`error`) para exibir num UAlert.
 */
export function useFormSubmit(form: Readonly<Ref<{ setErrors: (errors: FormError[]) => void } | null | undefined>>) {
  const error = ref<string | null>(null)

  async function run(action: () => Promise<unknown>): Promise<boolean> {
    error.value = null
    try {
      await action()
      return true
    }
    catch (e) {
      const fieldErrors = toFormErrors(e)
      if (fieldErrors.length) form.value?.setErrors(fieldErrors)
      else error.value = errorMessage(e)
      return false
    }
  }

  return { error, run }
}
