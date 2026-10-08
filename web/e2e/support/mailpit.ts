const MAILPIT_URL = process.env.MAILPIT_URL ?? 'http://localhost:8026'

interface MailpitSearch {
  messages: { ID: string }[]
}

interface MailpitMessage {
  HTML: string
  Text: string
}

/** Aguarda o e-mail mais recente enviado para `to` e retorna o primeiro link que contém `match`. */
export async function linkFromLatestEmail(to: string, match: string, timeoutMs = 15_000): Promise<string> {
  const deadline = Date.now() + timeoutMs
  while (Date.now() < deadline) {
    const res = await fetch(`${MAILPIT_URL}/api/v1/search?query=${encodeURIComponent(`to:"${to}"`)}`)
    const search = (await res.json()) as MailpitSearch
    const id = search.messages[0]?.ID
    if (id) {
      const message = (await (await fetch(`${MAILPIT_URL}/api/v1/message/${id}`)).json()) as MailpitMessage
      const links = [...message.HTML.matchAll(/href="([^"]+)"/g)].map(m => m[1]!.replaceAll('&amp;', '&'))
      const link = links.find(l => l.includes(match))
      if (link) return link
    }
    await new Promise(r => setTimeout(r, 500))
  }
  throw new Error(`Nenhum e-mail para ${to} com link contendo "${match}"`)
}

export function uniqueEmail(prefix = 'e2e'): string {
  return `${prefix}-${Date.now()}-${Math.floor(Math.random() * 1e6)}@exemplo.test`
}
