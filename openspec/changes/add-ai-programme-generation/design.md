# Design: AI programme generation

The API creates an `AiGeneration` in `en_attente` and responds with HTTP 202. A queued job handles provider latency. Only the coach assigned to a member can create the request, and the complete sport-profile context is persisted for auditability.

The agent uses Laravel AI SDK structured output. Successful jobs will create a linked Programme with `statut=brouillon` and `source=ia`; validation and publication remain separate coach workflows.
