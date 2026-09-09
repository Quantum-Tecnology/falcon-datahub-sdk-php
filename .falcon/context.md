---
status: active
produto: SDK PHP do DataHub
---

## O que é e por que existe

Cliente PHP da API do DataHub, usado pelos outros backends Falcon para consultar CNPJ, CEP, cotações e candles sem reimplementar autenticação e retry.

É pacote: chega nos projetos pelo composer, então mudança aqui afeta quem já
está em produção. Versionar com cuidado.

## Pegadinhas

- 🟠 **Quem consome trava a versão no `composer.lock`** — subir um comportamento
  novo aqui não chega sozinho nos serviços; é preciso atualizar cada um.

> Curadoria ainda rasa: escrever o *porquê* das decisões conforme o pacote for
> tocado. Formato no `CLAUDE.md` do Engineering Hub.
