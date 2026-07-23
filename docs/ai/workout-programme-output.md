# Workout Programme AI Output Contract

GymFlow requests a structured weekly programme from the AI agent. The application stores the response as an AI generation record and creates a programme with `source = ia` and `statut = brouillon`. The AI must not decide validation or publication.

## Top-level object

| Field | Type | Required | Meaning |
| --- | --- | --- | --- |
| `titre` | string | Yes | Clear weekly programme title. |
| `sessions` | array | Yes, minimum 1 | Ordered training sessions for the week. |

## Session object

| Field | Type | Required | Meaning |
| --- | --- | --- | --- |
| `jour` | string | Yes | Training day label, ideally from the member's available days. |
| `notes` | string | Yes | Coach-facing session guidance and safety context. |
| `exercices` | array | Yes, minimum 1 | Ordered exercises in the session. |

## Exercise object

| Field | Type | Required | Meaning |
| --- | --- | --- | --- |
| `nom` | string | Yes | Exercise name. |
| `groupe_musculaire` | string | Yes | Main targeted muscle group. |
| `type` | enum | Yes | `musculation`, `cardio`, or `mobilite`. |
| `series` | integer | Yes | Number of sets; use `0` when not applicable. |
| `repetitions` | integer | Yes | Repetitions per set; use `0` when not applicable. |
| `repos` | string | Yes | Rest instruction; explain when not applicable. |
| `duree_cardio` | integer | Yes | Cardio duration in minutes; use `0` when not applicable. |
| `notes` | string | Yes | Execution and safety guidance. |
| `progression` | string | Yes | Conservative progression instruction. |

## Safety and review rules

- The output must account for injuries, restrictions, level, preferences, days, and coach constraints provided in the prompt.
- It must not contain medical diagnosis or treatment.
- The response is a coach-review draft. Only GymFlow's coach workflow can validate and publish it.
